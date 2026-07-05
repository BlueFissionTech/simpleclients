<?php

namespace BlueFission\SimpleClients;

use BlueFission\Arr;
use BlueFission\Connections\IO;
use BlueFission\Func;
use BlueFission\Net\HTTP;
use BlueFission\Str;
use BlueFission\Val;

class HttpJson
{
    private static $fetcher = null;

    public static function fetchUsing(?callable $fetcher): void
    {
        self::$fetcher = $fetcher;
    }

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
        $decoded = HTTP::jsonDecode($body, true, $fallback);

        return Arr::is($decoded) ? $decoded : $fallback;
    }

    private static function fetch(string $method, string $url, array $headers = [], $body = null): string
    {
        $fetcher = self::$fetcher;
        if (Func::isCallable($fetcher)) {
            $response = $fetcher($method, $url, $headers, $body);

            return is_string($response) ? $response : '';
        }

        $method = Str::upper($method);
        if (self::canUseIoFetch($method, $headers, $body)) {
            $response = IO::fetch($url);

            return is_string($response) ? $response : '';
        }

        $options = [
            'http' => [
                'method' => $method,
            ],
        ];

        $header = self::headerString($headers);
        if ($header !== '') {
            $options['http']['header'] = $header;
        }

        if (Val::isNotNull($body) && $method !== 'GET') {
            $options['http']['content'] = self::encode($body);
        }

        $response = @file_get_contents($url, false, stream_context_create($options));

        return is_string($response) ? $response : '';
    }

    private static function headerString(array $headers): string
    {
        $lines = [];
        foreach ($headers as $name => $value) {
            $lines[] = is_int($name) ? (string)$value : self::headerLine((string)$name, (string)$value);
        }

        return Arr::make($lines)->join("\r\n")->val();
    }

    private static function headerLine(string $name, string $value): string
    {
        return HTTP::headerLine($name, $value);
    }

    private static function encode($body): string
    {
        if (Arr::is($body)) {
            return (string)HTTP::jsonEncode($body);
        }

        return (string)$body;
    }

    private static function canUseIoFetch(string $method, array $headers, $body): bool
    {
        return $method === 'GET'
            && Arr::size($headers) === 0
            && Val::isNull($body)
            && method_exists(IO::class, 'fetch')
            && !function_exists(__NAMESPACE__ . '\\file_get_contents');
    }
}
