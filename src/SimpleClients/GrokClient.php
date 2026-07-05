<?php

namespace BlueFission\SimpleClients;

use BlueFission\Arr;
use BlueFission\SimpleClients\Concerns\ProviderClientHelpers;
use BlueFission\SimpleClients\Cloud\HttpClient;
use BlueFission\Str;
use BlueFission\Val;

class GrokClient
{
    use ProviderClientHelpers;

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
            $this->providerEncode($this->payload($input, $config))
        );

        $body = (string)($response['body'] ?? '');
        $decoded = $this->providerJson($body);
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
        if (
            Arr::is($input)
            && Arr::hasKey($input, 0)
            && Arr::is($input[0])
            && Arr::hasKey($input[0], 'role')
            && Arr::hasKey($input[0], 'content')
        ) {
            return $input;
        }

        return [
            [
                'role' => 'user',
                'content' => $this->providerPrompt($input),
            ],
        ];
    }

    private function headers(array $config): array
    {
        $headers = $config['headers'] ?? [];
        $headers = Arr::is($headers) ? Arr::make($headers)->toArray() : [];
        $resolved = Arr::make([
            'Authorization' => 'Bearer ' . $this->_apiKey,
            'Content-Type' => 'application/json',
        ])->toArray();

        foreach ($headers as $name => $value) {
            $resolved[$name] = $value;
        }

        return $resolved;
    }

    private function extractText(array $response): string
    {
        $choice = $response['choices'][0] ?? [];
        if (Arr::is($choice)) {
            $message = $choice['message'] ?? [];
            if (Arr::is($message) && Arr::hasKey($message, 'content')) {
                return (string)$message['content'];
            }

            if (Arr::hasKey($choice, 'text')) {
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
        if (Arr::is($error) && Arr::hasKey($error, 'message') && Val::isNotEmpty($error['message'])) {
            return (string)$error['message'];
        }

        return (string)($response['message'] ?? 'Grok request failed.');
    }
}
