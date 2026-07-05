<?php

namespace BlueFission\SimpleClients\Aws;

use BlueFission\Arr;
use BlueFission\Net\HTTP;
use BlueFission\Str;
use BlueFission\Val;

class SigV4
{
    public function sign(
        string $method,
        string $url,
        array $headers,
        string $body,
        string $region,
        string $service,
        string $accessKey,
        string $secretKey,
        ?string $sessionToken = null,
        ?string $amzDate = null
    ): array {
        $method = Str::upper($method);
        $body = Val::isNull($body) ? '' : $body;
        $amzDate = $amzDate ?? gmdate('Ymd\\THis\\Z');
        $dateStamp = Str::sub($amzDate, 0, 8);

        $parts = HTTP::urlParts($url) ?? [];
        $host = $parts['host'] ?? '';
        $path = $parts['path'] ?? '/';
        $query = $parts['query'] ?? '';

        $headers = $this->normalizeHeaders($headers);
        $headers['host'] = $host;
        $headers['x-amz-date'] = $amzDate;
        if ($sessionToken) {
            $headers['x-amz-security-token'] = $sessionToken;
        }

        $signedHeaders = $this->signedHeaders($headers);
        $canonicalHeaders = $this->canonicalHeaders($headers);
        $canonicalQuery = $this->canonicalQuery($query);
        $canonicalUri = $this->canonicalUri($path);
        $payloadHash = hash('sha256', $body);

        $canonicalRequest = Arr::make([
            $method,
            $canonicalUri,
            $canonicalQuery,
            $canonicalHeaders,
            $signedHeaders,
            $payloadHash,
        ])->join("\n")->val();

        $credentialScope = $dateStamp . '/' . $region . '/' . $service . '/aws4_request';
        $stringToSign = Arr::make([
            'AWS4-HMAC-SHA256',
            $amzDate,
            $credentialScope,
            hash('sha256', $canonicalRequest),
        ])->join("\n")->val();

        $signingKey = $this->signingKey($secretKey, $dateStamp, $region, $service);
        $signature = hash_hmac('sha256', $stringToSign, $signingKey);

        $authorization = sprintf(
            'AWS4-HMAC-SHA256 Credential=%s/%s, SignedHeaders=%s, Signature=%s',
            $accessKey,
            $credentialScope,
            $signedHeaders,
            $signature
        );

        $headers['authorization'] = $authorization;

        return $this->formatHeaders($headers);
    }

    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $key = Str::lower(Str::trim((string)$name));
            $normalized[$key] = Str::trim(Arr::is($value) ? Arr::make($value)->join(',')->val() : (string)$value);
        }
        return $normalized;
    }

    private function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $name => $value) {
            $formatted[] = $name . ': ' . $value;
        }
        return $formatted;
    }

    private function signedHeaders(array $headers): string
    {
        $keys = Arr::make($headers)->keys()->val();
        sort($keys);
        return Arr::make($keys)->join(';')->val();
    }

    private function canonicalHeaders(array $headers): string
    {
        $keys = Arr::make($headers)->keys()->val();
        sort($keys);
        $lines = [];
        foreach ($keys as $key) {
            $lines[] = $key . ':' . preg_replace('/\s+/', ' ', (string)$headers[$key]);
        }
        return Arr::make($lines)->join("\n")->val() . "\n";
    }

    private function canonicalQuery(string $query): string
    {
        if (Val::isEmpty($query)) {
            return '';
        }

        parse_str($query, $params);
        $pairs = [];
        foreach ($params as $key => $value) {
            if (Arr::is($value)) {
                sort($value);
                foreach ($value as $item) {
                    $pairs[] = $this->encode($key) . '=' . $this->encode($item);
                }
            } else {
                $pairs[] = $this->encode($key) . '=' . $this->encode($value);
            }
        }

        sort($pairs);
        return Arr::make($pairs)->join('&')->val();
    }

    private function canonicalUri(string $path): string
    {
        $segments = Str::make($path)
            ->split('/')
            ->map(fn ($segment) => rawurlencode($segment))
            ->toArray();

        return Arr::make($segments)->join('/')->val();
    }

    private function encode(string $value): string
    {
        return Str::replace(rawurlencode($value), '%7E', '~');
    }

    private function signingKey(string $secretKey, string $dateStamp, string $region, string $service): string
    {
        $kDate = hash_hmac('sha256', $dateStamp, 'AWS4' . $secretKey, true);
        $kRegion = hash_hmac('sha256', $region, $kDate, true);
        $kService = hash_hmac('sha256', $service, $kRegion, true);
        return hash_hmac('sha256', 'aws4_request', $kService, true);
    }
}
