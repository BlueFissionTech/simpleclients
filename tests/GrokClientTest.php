<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\GrokClient;
use PHPUnit\Framework\TestCase;

class GrokClientTest extends TestCase
{
    public function testCompleteReturnsMockMessage(): void
    {
        $client = new GrokClient('key', 'https://api.grok.test');
        $response = $client->complete('Sample prompt', ['temperature' => 0.2]);

        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsString('Sample prompt', $response['message']);
        $this->assertSame(['temperature' => 0.2], $response['config']);
        $this->assertSame('https://api.grok.test', $response['base_url']);
    }

    public function testRespondAliasesComplete(): void
    {
        $client = new GrokClient('key');
        $response = $client->respond('Chat here');

        $this->assertArrayHasKey('message', $response);
        $this->assertStringContainsString('Chat here', $response['message']);
    }
}
