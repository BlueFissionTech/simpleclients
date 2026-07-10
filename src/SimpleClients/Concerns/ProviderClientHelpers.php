<?php

namespace BlueFission\SimpleClients\Concerns;

use BlueFission\Arr;
use BlueFission\Connections\IO;
use BlueFission\Net\HTTP;
use BlueFission\Obj;
use BlueFission\SimpleClients\Runtime;
use BlueFission\Str;
use BlueFission\Val;

trait ProviderClientHelpers
{
    private function providerName(array $config, string $default = 'gcp'): string
    {
        return Str::lower((string)($config['provider'] ?? $default));
    }

    private function providerConfig(array $base, array $override = []): array
    {
        $config = Arr::make($base)->toArray();

        foreach ($override as $key => $value) {
            $config[$key] = $value;
        }

        return $config;
    }

    private function providerHas(array $source, string|int $key): bool
    {
        return Arr::hasKey($source, $key) && Val::isNotEmpty($source[$key]);
    }

    private function providerPath(array|object $source, string|array $path, mixed $fallback = null): mixed
    {
        return Arr::make($source)->getPath($path, $fallback);
    }

    private function providerQuery(array $query): string
    {
        return Arr::isNotEmpty($query) ? HTTP::query($query) : '';
    }

    private function providerAppendQuery(string $url, array $query): string
    {
        $queryString = $this->providerQuery($query);

        if (Val::isEmpty($queryString)) {
            return $url;
        }

        return $url . (Str::has($url, '?') ? '&' : '?') . $queryString;
    }

    private function providerAppendParam(string $url, string $name, mixed $value): string
    {
        return $this->providerAppendQuery($url, [$name => $value]);
    }

    private function providerEncode(mixed $value): string
    {
        if (Arr::is($value)) {
            return (string)HTTP::jsonEncode($value);
        }

        return (string)$value;
    }

    private function providerJoin(array $parts, string $separator = ''): string
    {
        return Arr::make($parts)->join($separator)->val();
    }

    private function providerPrompt($input): string
    {
        if ($input instanceof Obj && Runtime::canCall($input, 'prompt')) {
            return (string)$input->prompt();
        }

        if (Runtime::objectCanCall($input, 'prompt')) {
            return (string)$input->prompt();
        }

        return (string)$input;
    }

    private function normalizeProviderInput($input): array
    {
        if (Arr::is($input) && Arr::hasKey($input, 'type') && Arr::hasKey($input, 'value')) {
            return $input;
        }

        if (Str::is($input) && HTTP::urlScheme($input) && HTTP::urlHost($input)) {
            return ['type' => 'url', 'value' => $input];
        }

        if (Str::is($input) && is_file($input)) {
            return ['type' => 'bytes', 'value' => $this->providerFileBytes($input)];
        }

        return ['type' => 'bytes', 'value' => (string)$input];
    }

    private function providerError(array $payload, string $message): array
    {
        $payload['error'] = $message;
        $payload['raw'] = [];

        return $payload;
    }

    private function providerFileBytes(string $path): string
    {
        if (Runtime::canCall(IO::class, 'input')) {
            $bytes = IO::input($path);
        } else {
            $bytes = IO::std($path);
        }

        return is_string($bytes) ? $bytes : '';
    }

    private function providerJson(string $body, array $fallback = []): array
    {
        $decoded = $this->providerJsonValue($body, $fallback);

        return Arr::is($decoded) ? $decoded : $fallback;
    }

    private function providerJsonValue(string $body, mixed $fallback = null): mixed
    {
        return HTTP::jsonDecode($body, true, $fallback);
    }
}
