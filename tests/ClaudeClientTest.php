<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\ClaudeClient;
use PHPUnit\Framework\TestCase;

class ClaudeClientTest extends TestCase
{
    public function testCompleteReturnsMockResponse(): void
    {
        $client = new ClaudeClient('key');
        $response = $client->complete('Hello world', ['max_tokens' => 50]);

        $this->assertArrayHasKey('completion', $response);
        $this->assertStringContainsString('Hello world', $response['completion']);
        $this->assertSame(['max_tokens' => 50], $response['config']);
    }

    public function testRespondAliasesComplete(): void
    {
        $client = new ClaudeClient('key');
        $response = $client->respond('Chat message');

        $this->assertArrayHasKey('completion', $response);
        $this->assertStringContainsString('Chat message', $response['completion']);
    }
}
