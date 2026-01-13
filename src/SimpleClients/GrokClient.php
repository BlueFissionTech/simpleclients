<?php

namespace BlueFission\SimpleClients;

class GrokClient
{
    private string $_apiKey;
    private string $_baseUrl;

    public function __construct(string $apiKey, string $baseUrl = 'https://api.grok.com')
    {
        $this->_apiKey = $apiKey;
        $this->_baseUrl = rtrim($baseUrl, '/');
    }

    public function generate($input, $config = [], ?callable $callback = null): array
    {
        return $this->complete($input, $config);
    }

    public function complete($input, $config = []): array
    {
        $prompt = $this->normalizeInput($input);

        return [
            'message' => 'Grok mock response for: ' . mb_substr((string)$prompt, 0, 80),
            'config' => $config,
            'base_url' => $this->_baseUrl,
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
}
