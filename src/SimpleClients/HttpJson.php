<?php

namespace BlueFission\SimpleClients;

use BlueFission\Arr;
use BlueFission\Str;

class HttpJson
{
    public static function get(string $url, array $fallback = []): array
    {
        return self::request('GET', $url, [], null, $fallback);
    }

    public static function request(string $method, string $url, array $headers = [], $body = null, array $fallback = []): array
    {
        return self::decode(self::fetch($method, $url, $headers, $body), $fallback);
    }

    public static function decode(string $body, array $fallback = []): array
    {
        $decoded = json_decode($body, true);

        return Arr::is($decoded) ? $decoded : $fallback;
    }

    private static function fetch(string $method, string $url, array $headers = [], $body = null): string
    {
        $options = [
            'http' => [
                'method' => Str::upper($method),
            ],
        ];

        $header = self::headerString($headers);
        if ($header !== '') {
            $options['http']['header'] = $header;
        }

        if (!is_null($body) && Str::upper($method) !== 'GET') {
            $options['http']['content'] = Arr::is($body) ? json_encode($body) : (string)$body;
        }

        $response = @file_get_contents($url, false, stream_context_create($options));

        return is_string($response) ? $response : '';
    }

    private static function headerString(array $headers): string
    {
        $lines = [];
        foreach ($headers as $name => $value) {
            $lines[] = is_int($name) ? (string)$value : $name . ': ' . (string)$value;
        }

        return implode("\r\n", $lines);
    }
}
