<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\Aws\SigV4;
use PHPUnit\Framework\TestCase;

class SigV4Test extends TestCase
{
    public function testSignAddsAuthorizationAndDate(): void
    {
        $signer = new SigV4();
        $headers = $signer->sign(
            'POST',
            'https://example.amazonaws.com/test?b=1&a=2',
            ['Content-Type' => 'application/json'],
            '{}',
            'us-east-1',
            'service',
            'AKIDEXAMPLE',
            'SECRET',
            null,
            '20250101T120000Z'
        );

        $headerMap = $this->mapHeaders($headers);

        $this->assertArrayHasKey('authorization', $headerMap);
        $this->assertArrayHasKey('x-amz-date', $headerMap);
        $this->assertArrayHasKey('host', $headerMap);
        $this->assertSame('20250101T120000Z', $headerMap['x-amz-date']);
        $this->assertStringContainsString('Credential=AKIDEXAMPLE/20250101/us-east-1/service/aws4_request', $headerMap['authorization']);
        $this->assertStringContainsString('SignedHeaders=content-type;host;x-amz-date', $headerMap['authorization']);
    }

    public function testSignatureChangesWithBody(): void
    {
        $signer = new SigV4();
        $headers1 = $signer->sign(
            'POST',
            'https://example.amazonaws.com/test',
            ['Content-Type' => 'application/json'],
            '{"a":1}',
            'us-east-1',
            'service',
            'AKIDEXAMPLE',
            'SECRET',
            null,
            '20250101T120000Z'
        );
        $headers2 = $signer->sign(
            'POST',
            'https://example.amazonaws.com/test',
            ['Content-Type' => 'application/json'],
            '{"a":2}',
            'us-east-1',
            'service',
            'AKIDEXAMPLE',
            'SECRET',
            null,
            '20250101T120000Z'
        );

        $auth1 = $this->mapHeaders($headers1)['authorization'] ?? '';
        $auth2 = $this->mapHeaders($headers2)['authorization'] ?? '';

        $this->assertNotSame($auth1, $auth2);
    }

    private function mapHeaders(array $headers): array
    {
        $mapped = [];
        foreach ($headers as $header) {
            $parts = explode(':', $header, 2);
            $name = strtolower(trim($parts[0] ?? ''));
            $value = trim($parts[1] ?? '');
            if ($name !== '') {
                $mapped[$name] = $value;
            }
        }

        return $mapped;
    }
}
