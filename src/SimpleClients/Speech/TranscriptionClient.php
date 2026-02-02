<?php

namespace BlueFission\SimpleClients\Speech;

use BlueFission\SimpleClients\Aws\SigV4;
use BlueFission\SimpleClients\Cloud\HttpClient;
use BlueFission\Arr;
use BlueFission\Str;
use BlueFission\Val;

class TranscriptionClient
{
    private $http;
    private array $config;

    public function __construct(array $config = [], $http = null)
    {
        $this->config = $config;
        $this->http = $http ?? new HttpClient();
    }

    public function transcribe($input, array $config = []): array
    {
        $config = array_merge($this->config, $config);
        $provider = Str::lower((string)($config['provider'] ?? 'gcp'));

        return match ($provider) {
            'azure' => $this->transcribeAzure($input, $config),
            'aws' => $this->transcribeAws($input, $config),
            default => $this->transcribeGcp($input, $config),
        };
    }

    private function transcribeGcp($input, array $config): array
    {
        $endpoint = $config['endpoint'] ?? 'https://speech.googleapis.com/v1/speech:recognize';
        $token = $config['token'] ?? null;
        $apiKey = $config['api_key'] ?? null;

        $audio = $this->normalizeInput($input);
        $payload = [
            'config' => $config['gcp_config'] ?? ['languageCode' => 'en-US'],
            'audio' => $audio['type'] === 'url'
                ? ['uri' => $audio['value']]
                : ['content' => base64_encode((string)$audio['value'])],
        ];

        $headers = ['Content-Type: application/json'];
        if (Val::isNotEmpty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        } elseif (Val::isNotEmpty($apiKey)) {
            $endpoint .= (Str::has($endpoint, '?') ? '&' : '?') . 'key=' . urlencode($apiKey);
        }

        $response = $this->http->request('POST', $endpoint, $headers, $payload);
        return $this->normalizeGcp($response['body']);
    }

    private function transcribeAzure($input, array $config): array
    {
        $endpoint = Str::trim((string)($config['endpoint'] ?? 'https://eastus.stt.speech.microsoft.com'), '/');
        $path = $config['path'] ?? '/speech/recognition/conversation/cognitiveservices/v1';
        $url = $endpoint . $path;
        $token = $config['token'] ?? null;
        $apiKey = $config['api_key'] ?? null;
        $contentType = $config['content_type'] ?? 'audio/wav';
        $query = $config['query'] ?? [];
        if (!isset($query['language'])) {
            $query['language'] = $config['language'] ?? 'en-US';
        }

        $headers = ['Content-Type: ' . $contentType];
        if (Val::isNotEmpty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        } elseif (Val::isNotEmpty($apiKey)) {
            $headers[] = 'Ocp-Apim-Subscription-Key: ' . $apiKey;
        }

        if (Arr::isNotEmpty($query)) {
            $url .= (Str::has($url, '?') ? '&' : '?') . http_build_query($query);
        }

        $audio = $this->normalizeInput($input);
        if ($audio['type'] === 'url') {
            return $this->errorResponse('Azure v1 transcription expects raw audio bytes; provide file bytes or use presigned download then pass bytes.');
        }

        $response = $this->http->request('POST', $url, $headers, $audio['value']);
        return $this->normalizeAzure($response['body']);
    }

    private function transcribeAws($input, array $config): array
    {
        $region = $config['region'] ?? 'us-east-1';
        $endpoint = $config['endpoint'] ?? "https://transcribe.{$region}.amazonaws.com";
        $accessKey = $config['access_key'] ?? null;
        $secretKey = $config['secret_key'] ?? null;
        $sessionToken = $config['session_token'] ?? null;

        $mediaUri = $config['media_uri'] ?? null;
        if (!$mediaUri) {
            $audio = $this->normalizeInput($input);
            if ($audio['type'] === 'url') {
                $mediaUri = $audio['value'];
            }
        }

        if (Val::isEmpty($mediaUri)) {
            return $this->errorResponse('AWS Transcribe requires a media_uri (S3/presigned URL).');
        }

        $payload = [
            'TranscriptionJobName' => $config['job_name'] ?? ('job-' . uniqid()),
            'LanguageCode' => $config['language_code'] ?? 'en-US',
            'MediaFormat' => $config['media_format'] ?? 'wav',
            'Media' => ['MediaFileUri' => $mediaUri],
        ];

        $headers = [
            'Content-Type: application/x-amz-json-1.1',
            'X-Amz-Target: Transcribe.StartTranscriptionJob',
        ];

        if (Val::isNotEmpty($accessKey) && Val::isNotEmpty($secretKey)) {
            $signer = new SigV4();
            $headers = $signer->sign('POST', $endpoint, $headers, json_encode($payload), $region, 'transcribe', $accessKey, $secretKey, $sessionToken);
        } else {
            return $this->errorResponse('AWS credentials required for Transcribe.');
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
        $results = $data['results'][0]['alternatives'][0] ?? [];

        return [
            'text' => $results['transcript'] ?? '',
            'confidence' => $results['confidence'] ?? null,
            'segments' => $data['results'] ?? [],
            'raw' => $data,
        ];
    }

    private function normalizeAzure(string $body): array
    {
        $data = json_decode($body, true);
        if (!Arr::is($data)) {
            return ['text' => Str::trim($body), 'confidence' => null, 'segments' => [], 'raw' => $body];
        }

        return [
            'text' => $data['DisplayText'] ?? ($data['Text'] ?? ''),
            'confidence' => $data['Confidence'] ?? null,
            'segments' => $data['NBest'] ?? [],
            'raw' => $data,
        ];
    }

    private function normalizeAws(string $body): array
    {
        $data = json_decode($body, true) ?? [];
        $job = $data['TranscriptionJob'] ?? [];

        return [
            'text' => $job['Transcript']['TranscriptFileUri'] ?? '',
            'confidence' => null,
            'segments' => $job,
            'raw' => $data,
        ];
    }

    private function errorResponse(string $message): array
    {
        return [
            'text' => '',
            'confidence' => null,
            'segments' => [],
            'error' => $message,
            'raw' => [],
        ];
    }
}
