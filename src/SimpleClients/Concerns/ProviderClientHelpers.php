<?php

namespace BlueFission\SimpleClients\Concerns;

use BlueFission\Arr;
use BlueFission\Connections\IO;
use BlueFission\Net\HTTP;
use BlueFission\Str;

trait ProviderClientHelpers
{
    private function providerName(array $config, string $default = 'gcp'): string
    {
        return Str::lower((string)($config['provider'] ?? $default));
    }

    private function normalizeProviderInput($input): array
    {
        if (Arr::is($input) && isset($input['type'], $input['value'])) {
            return $input;
        }

        if (Str::is($input) && filter_var($input, FILTER_VALIDATE_URL)) {
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
        if (method_exists(IO::class, 'input')) {
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
        if (method_exists(HTTP::class, 'jsonDecode')) {
            return HTTP::jsonDecode($body, true, $fallback);
        }

        return json_decode($body, true) ?? $fallback;
    }
}
