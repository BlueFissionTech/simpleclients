<?php

namespace BlueFission\SimpleClients;

use BlueFission\Arr;
use BlueFission\SimpleClients\Cloud\HttpClient;
use BlueFission\Str;

class GrokClient
{
    private string $_apiKey;
    private string $_baseUrl;
    private $_client;

    public function __construct(string $apiKey, string $baseUrl = 'https://api.x.ai', $client = null)
    {
        $this->_apiKey = $apiKey;
        $this->_baseUrl = Str::trim($baseUrl, '/');
        $this->_client = $client ?? new HttpClient();
    }

    public function generate($input, $config = [], ?callable $callback = null): array
    {
        $response = $this->complete($input, $config);

        if ($callback) {
            $callback($response);
        }

        return $response;
    }

    public function complete($input, $config = []): array
    {
        $response = $this->_client->request(
            'POST',
            $this->_baseUrl . '/v1/chat/completions',
            $this->headers($config),
            json_encode($this->payload($input, $config))
        );

        $body = (string)($response['body'] ?? '');
        $decoded = json_decode($body, true);
        $decoded = Arr::is($decoded) ? $decoded : [];
        $status = (int)($response['status'] ?? 0);

        return [
            'message' => $this->extractText($decoded),
            'status' => $status,
            'config' => $config,
            'base_url' => $this->_baseUrl,
            'response' => $decoded,
            'error' => $this->extractError($decoded, $status),
        ];
    }

    public function respond($input, $config = []): array
    {
        return $this->complete($input, $config);
    }

    private function normalizeInput($input): string
    {
        if (is_object($input) && method_exists($input, 'prompt')) {
            return (string)$input->prompt();
        }

        return (string)$input;
    }

    private function payload($input, array $config): array
    {
        $payload = $config;
        unset($payload['headers']);

        $payload['model'] = (string)($payload['model'] ?? 'latest');
        $payload['messages'] = $this->messages($input);

        return $payload;
    }

    private function messages($input): array
    {
        if (Arr::is($input) && isset($input[0]) && Arr::is($input[0]) && isset($input[0]['role'], $input[0]['content'])) {
            return $input;
        }

        return [
            [
                'role' => 'user',
                'content' => $this->normalizeInput($input),
            ],
        ];
    }

    private function headers(array $config): array
    {
        $headers = $config['headers'] ?? [];
        $headers = Arr::is($headers) ? $headers : [];

        return array_merge([
            'Authorization' => 'Bearer ' . $this->_apiKey,
            'Content-Type' => 'application/json',
        ], $headers);
    }

    private function extractText(array $response): string
    {
        $choice = $response['choices'][0] ?? [];
        if (Arr::is($choice)) {
            $message = $choice['message'] ?? [];
            if (Arr::is($message) && isset($message['content'])) {
                return (string)$message['content'];
            }

            if (isset($choice['text'])) {
                return (string)$choice['text'];
            }
        }

        return (string)($response['message'] ?? '');
    }

    private function extractError(array $response, int $status): string
    {
        if ($status < 400) {
            return '';
        }

        $error = $response['error'] ?? [];
        if (Arr::is($error) && isset($error['message'])) {
            return (string)$error['message'];
        }

        return (string)($response['message'] ?? 'Grok request failed.');
    }
}
