<?php

namespace BlueFission\SimpleClients\Cloud;

use BlueFission\Connections\Curl;
use BlueFission\Str;
use BlueFission\Val;

class HttpClient
{
    private Curl $curl;

    public function __construct(?Curl $curl = null)
    {
        $this->curl = $curl ?? new Curl();
    }

    public function request(string $method, string $url, array $headers = [], $body = null): array
    {
        $this->curl->config([
            'target' => $url,
            'method' => Str::lower($method),
            'headers' => $headers,
        ]);

        $this->curl->open();
        $this->curl->query(Val::isNull($body) ? null : $body);

        $status = 0;
        $connection = $this->curl->connection();
        if ($connection) {
            $info = curl_getinfo($connection);
            $status = $info['http_code'] ?? 0;
        }

        $body = (string)$this->curl->result();
        $this->curl->close();

        return [
            'status' => $status,
            'body' => $body,
            'headers' => [],
        ];
    }
}
