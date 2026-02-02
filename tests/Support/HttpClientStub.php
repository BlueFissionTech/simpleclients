<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests\Support;

class HttpClientStub
{
    public array $calls = [];
    public array $response = [
        'status' => 200,
        'body' => '{}',
        'headers' => [],
    ];

    public function request(string $method, string $url, array $headers = [], $body = null): array
    {
        $this->calls[] = [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
        ];

        return $this->response;
    }
}
