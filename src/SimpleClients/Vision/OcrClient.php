<?php

namespace BlueFission\SimpleClients\Vision;

use BlueFission\SimpleClients\Aws\SigV4;
use BlueFission\SimpleClients\Cloud\HttpClient;
use BlueFission\Arr;
use BlueFission\Str;
use BlueFission\Val;

class OcrClient
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
        $endpoint = $config['endpoint'] ?? 'https://vision.googleapis.com/v1/images:annotate';
        $token = $config['token'] ?? null;
        $apiKey = $config['api_key'] ?? null;

        $payload = [
            'requests' => [[
                'features' => $config['features'] ?? [['type' => 'TEXT_DETECTION']],
                'image' => $this->gcpImagePayload($input),
            ]],
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

    private function analyzeAzure($input, array $config): array
    {
        $endpoint = Str::trim((string)($config['endpoint'] ?? 'https://example.cognitiveservices.azure.com'), '/');
        $path = $config['path'] ?? '/vision/v3.2/ocr';
        $url = $endpoint . $path;
        $token = $config['token'] ?? null;
        $apiKey = $config['api_key'] ?? null;
        $query = $config['query'] ?? [];
        if (!isset($query['language']) && Val::isNotEmpty($config['language'] ?? null)) {
            $query['language'] = $config['language'];
        }
        if (!isset($query['detectOrientation']) && Val::isNotEmpty($config['detect_orientation'] ?? null)) {
            $query['detectOrientation'] = $config['detect_orientation'];
        }

        $headers = [];
        if (Val::isNotEmpty($token)) {
            $headers[] = 'Authorization: Bearer ' . $token;
        } elseif (Val::isNotEmpty($apiKey)) {
            $headers[] = 'Ocp-Apim-Subscription-Key: ' . $apiKey;
        }

        if (Arr::isNotEmpty($query)) {
            $url .= (Str::has($url, '?') ? '&' : '?') . http_build_query($query);
        }

        $inputInfo = $this->normalizeInput($input);
        if ($inputInfo['type'] === 'url') {
            $headers[] = 'Content-Type: application/json';
            $body = ['url' => $inputInfo['value']];
        } else {
            $headers[] = 'Content-Type: application/octet-stream';
            $body = $inputInfo['value'];
        }

        $response = $this->http->request('POST', $url, $headers, $body);
        return $this->normalizeAzure($response['body']);
    }

    private function analyzeAws($input, array $config): array
    {
        $region = $config['region'] ?? 'us-east-1';
        $endpoint = $config['endpoint'] ?? "https://textract.{$region}.amazonaws.com";
        $accessKey = $config['access_key'] ?? null;
        $secretKey = $config['secret_key'] ?? null;
        $sessionToken = $config['session_token'] ?? null;

        $document = [];
        if (!empty($config['s3_bucket']) && !empty($config['s3_key'])) {
            $document['S3Object'] = [
                'Bucket' => $config['s3_bucket'],
                'Name' => $config['s3_key'],
            ];
        } else {
            $inputInfo = $this->normalizeInput($input);
            $document['Bytes'] = base64_encode((string)$inputInfo['value']);
        }

        $payload = [
            'Document' => $document,
        ];

        $headers = [
            'Content-Type: application/x-amz-json-1.1',
            'X-Amz-Target: Textract.DetectDocumentText',
        ];

        if (Val::isNotEmpty($accessKey) && Val::isNotEmpty($secretKey)) {
            $signer = new SigV4();
            $signed = $signer->sign('POST', $endpoint, $headers, json_encode($payload), $region, 'textract', $accessKey, $secretKey, $sessionToken);
            $headers = $signed;
        } else {
            return $this->errorResponse('AWS credentials required for Textract OCR.');
        }

        $response = $this->http->request('POST', $endpoint, $headers, $payload);
        return $this->normalizeAws($response['body']);
    }

    private function gcpImagePayload($input): array
    {
        $inputInfo = $this->normalizeInput($input);
        if ($inputInfo['type'] === 'url') {
            return ['source' => ['imageUri' => $inputInfo['value']]];
        }

        return ['content' => base64_encode((string)$inputInfo['value'])];
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
        $response = $data['responses'][0] ?? [];
        $text = $response['textAnnotations'][0]['description'] ?? '';
        $blocks = [];
        foreach (($response['textAnnotations'] ?? []) as $item) {
            if (!isset($item['description'])) {
                continue;
            }
            $blocks[] = [
                'text' => $item['description'],
                'bounding_poly' => $item['boundingPoly'] ?? null,
            ];
        }

        return [
            'text' => $text,
            'blocks' => $blocks,
            'entities' => [],
            'confidence' => $response['textAnnotations'][0]['score'] ?? null,
            'raw' => $data,
        ];
    }

    private function normalizeAzure(string $body): array
    {
        $data = json_decode($body, true) ?? [];
        $lines = [];
        foreach (($data['regions'] ?? []) as $region) {
            foreach (($region['lines'] ?? []) as $line) {
                $words = array_map(fn ($w) => $w['text'] ?? '', $line['words'] ?? []);
                $lines[] = Str::trim(implode(' ', $words));
            }
        }

        return [
            'text' => Str::trim(implode("\n", $lines)),
            'blocks' => $lines,
            'entities' => [],
            'confidence' => null,
            'raw' => $data,
        ];
    }

    private function normalizeAws(string $body): array
    {
        $data = json_decode($body, true) ?? [];
        $lines = [];
        foreach (($data['Blocks'] ?? []) as $block) {
            if (($block['BlockType'] ?? '') === 'LINE') {
                $lines[] = $block['Text'] ?? '';
            }
        }

        return [
            'text' => Str::trim(implode("\n", $lines)),
            'blocks' => $lines,
            'entities' => [],
            'confidence' => null,
            'raw' => $data,
        ];
    }

    private function errorResponse(string $message): array
    {
        return [
            'text' => '',
            'blocks' => [],
            'entities' => [],
            'confidence' => null,
            'error' => $message,
            'raw' => [],
        ];
    }
}
