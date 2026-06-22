<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\HttpJson;
use PHPUnit\Framework\TestCase;

class HttpJsonTest extends TestCase
{
    protected function tearDown(): void
    {
        HttpJson::fetchUsing(null);
    }

    public function testDecodeReturnsFallbackForInvalidJson(): void
    {
        $this->assertSame(['ok' => false], HttpJson::decode('not-json', ['ok' => false]));
    }

    public function testRequestCanUseInjectedFetcher(): void
    {
        $seen = [];
        HttpJson::fetchUsing(function (string $method, string $url, array $headers, $body) use (&$seen): string {
            $seen = compact('method', 'url', 'headers', 'body');

            return '{"ok":true}';
        });

        $result = HttpJson::request(
            'POST',
            'https://api.example.test/records',
            ['Content-Type' => 'application/json'],
            ['name' => 'Ada']
        );

        $this->assertSame(['ok' => true], $result);
        $this->assertSame('POST', $seen['method']);
        $this->assertSame('https://api.example.test/records', $seen['url']);
        $this->assertSame(['Content-Type' => 'application/json'], $seen['headers']);
        $this->assertSame(['name' => 'Ada'], $seen['body']);
    }
}
