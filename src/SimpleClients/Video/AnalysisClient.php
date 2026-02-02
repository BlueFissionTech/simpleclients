<?php

namespace BlueFission\SimpleClients\Video;

use BlueFission\SimpleClients\Aws\SigV4;
use BlueFission\SimpleClients\Cloud\HttpClient;
use BlueFission\Arr;
use BlueFission\Str;
use BlueFission\Val;

class AnalysisClient
{
    private $http;
    private array $config;

    public function __construct(array $config = [], $http = null)
    {
        $this->config = $config;
        $this->http = $http ?? new HttpClient();
    }

    public function analyze($input, array $config = []): array
    {
        $config = array_merge($this->config, $config);
        $provider = Str::lower((string)($config['provider'] ?? 'gcp'));

        return match ($provider) {
            'azure' => $this->analyzeAzure($input, $config),
            'aws' => $this->analyzeAws($input, $config),
            default => $this->analyzeGcp($input, $config),
        };
    }

    private function analyzeGcp($input, array $config): array
    {
        $endpoint = $config['endpoint'] ?? 'https://videointelligence.googleapis.com/v1/videos:annotate';
        $token = $config['token'] ?? null;
        $apiKey = $config['api_key'] ?? null;

        $video = $this->normalizeInput($input);
        $payload = [
            'inputUri' => $video['type'] === 'url' ? $video['value'] : null,
            'features' => $config['features'] ?? ['LABEL_DETECTION'],
        ];
        if ($video['type'] !== 'url') {
            $payload['inputContent'] = base64_encode((string)$video['value']);
            unset($payload['inputUri']);
        }

        $headers = ['Content-Type: application/json'];
        if (Val::isNotEmpty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        } elseif (Val::isNotEmpty($apiKey)) {
            $endpoint .= (Str::has($endpoint, '?') ? '&' : '?') . 'key=' . urlencode($apiKey);
        }

        $response = $this->http->request('POST', $endpoint, $headers, $payload);
        return $this->normalizeGcp($response['body']);
    }

    private function analyzeAzure($input, array $config): array
    {
        $endpoint = Str::trim((string)($config['endpoint'] ?? 'https://api.videoindexer.ai'), '/');
        $location = $config['location'] ?? 'trial';
        $accountId = $config['account_id'] ?? '';
        $path = $config['path'] ?? "/{$location}/Accounts/{$accountId}/Videos";
        $url = $endpoint . $path;
        $token = $config['token'] ?? null;
        $apiKey = $config['api_key'] ?? null;
        $query = $config['query'] ?? [];
        if (Val::isNotEmpty($config['access_token'] ?? null)) {
            $query['accessToken'] = $config['access_token'];
        }
        $video = $this->normalizeInput($input);

        $headers = [];
        if (Val::isNotEmpty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        } elseif (Val::isNotEmpty($apiKey)) {
            $headers[] = 'Ocp-Apim-Subscription-Key: ' . $apiKey;
        }

        if (Val::isEmpty($accountId) && !isset($config['path'])) {
            return $this->errorResponse('Azure Video Indexer requires account_id or explicit path.');
        }

        if (Arr::isNotEmpty($query)) {
            $url .= (Str::has($url, '?') ? '&' : '?') . http_build_query($query);
        }

        $payload = [];
        if ($video['type'] === 'url') {
            $payload['videoUrl'] = $video['value'];
        } else {
            $payload['contentBytes'] = base64_encode((string)$video['value']);
        }

        $headers[] = 'Content-Type: application/json';
        $response = $this->http->request('POST', $url, $headers, $payload);
        return $this->normalizeAzure($response['body']);
    }

    private function analyzeAws($input, array $config): array
    {
        $region = $config['region'] ?? 'us-east-1';
        $endpoint = $config['endpoint'] ?? "https://rekognition.{$region}.amazonaws.com";
        $accessKey = $config['access_key'] ?? null;
        $secretKey = $config['secret_key'] ?? null;
        $sessionToken = $config['session_token'] ?? null;

        $video = $this->normalizeInput($input);
        $payload = [
            'Video' => [],
            'MinConfidence' => $config['min_confidence'] ?? 50,
        ];

        if ($video['type'] === 'url' && Val::isNotEmpty($config['s3_bucket'] ?? null) && Val::isNotEmpty($config['s3_key'] ?? null)) {
            $payload['Video']['S3Object'] = [
                'Bucket' => $config['s3_bucket'],
                'Name' => $config['s3_key'],
            ];
        } else {
            return $this->errorResponse('AWS Rekognition video analysis requires S3 bucket/key.');
        }

        $headers = [
            'Content-Type: application/x-amz-json-1.1',
            'X-Amz-Target: RekognitionService.StartLabelDetection',
        ];

        if (Val::isNotEmpty($accessKey) && Val::isNotEmpty($secretKey)) {
            $signer = new SigV4();
            $headers = $signer->sign('POST', $endpoint, $headers, json_encode($payload), $region, 'rekognition', $accessKey, $secretKey, $sessionToken);
        } else {
            return $this->errorResponse('AWS credentials required for Rekognition.');
        }

        $response = $this->http->request('POST', $endpoint, $headers, $payload);
        return $this->normalizeAws($response['body']);
    }

    private function normalizeInput($input): array
    {
        if (Arr::is($input) && isset($input['type'], $input['value'])) {
            return $input;
        }

        if (Str::is($input) && filter_var($input, FILTER_VALIDATE_URL)) {
            return ['type' => 'url', 'value' => $input];
        }

        if (Str::is($input) && is_file($input)) {
            return ['type' => 'bytes', 'value' => file_get_contents($input)];
        }

        return ['type' => 'bytes', 'value' => (string)$input];
    }

    private function normalizeGcp(string $body): array
    {
        $data = json_decode($body, true) ?? [];
        return [
            'labels' => $data['annotationResults'][0]['segmentLabelAnnotations'] ?? [],
            'raw' => $data,
        ];
    }

    private function normalizeAzure(string $body): array
    {
        $data = json_decode($body, true);
        if (!Arr::is($data)) {
            return ['labels' => [], 'raw' => $body];
        }

        return [
            'labels' => $data['videos'] ?? [],
            'raw' => $data,
        ];
    }

    private function normalizeAws(string $body): array
    {
        $data = json_decode($body, true) ?? [];
        return [
            'labels' => $data['Labels'] ?? [],
            'raw' => $data,
        ];
    }

    private function errorResponse(string $message): array
    {
        return [
            'labels' => [],
            'error' => $message,
            'raw' => [],
        ];
    }
}
