<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\ClaudeClient;
use BlueFission\SimpleClients\Tests\Support\HttpClientStub;
use PHPUnit\Framework\TestCase;

class ClaudeClientTest extends TestCase
{
    public function testCompleteCallsMessagesApiWithInjectedTransport(): void
    {
        $transport = new HttpClientStub();
        $transport->response['body'] = json_encode([
            'content' => [
                ['type' => 'text', 'text' => 'Claude fixture answer'],
            ],
        ]);

        $client = new ClaudeClient('key', 'https://api.anthropic.test', $transport);
        $response = $client->complete('Hello world', [
            'model' => 'claude-test',
            'max_tokens' => 50,
        ]);

        $this->assertSame('Claude fixture answer', $response['completion']);
        $this->assertSame(200, $response['status']);
        $this->assertSame('', $response['error']);
        $this->assertSame('POST', $transport->calls[0]['method']);
        $this->assertSame('https://api.anthropic.test/v1/messages', $transport->calls[0]['url']);
        $this->assertSame('key', $transport->calls[0]['headers']['x-api-key']);
        $this->assertSame('2023-06-01', $transport->calls[0]['headers']['anthropic-version']);

        $payload = json_decode($transport->calls[0]['body'], true);
        $this->assertSame('claude-test', $payload['model']);
        $this->assertSame(50, $payload['max_tokens']);
        $this->assertSame('Hello world', $payload['messages'][0]['content']);
    }

    public function testRespondAliasesComplete(): void
    {
        $transport = new HttpClientStub();
        $transport->response['body'] = json_encode([
            'content' => [
                ['type' => 'text', 'text' => 'Chat response'],
            ],
        ]);

        $client = new ClaudeClient('key', 'https://api.anthropic.test', $transport);
        $response = $client->respond('Chat message');

        $this->assertSame('Chat response', $response['completion']);
    }

    public function testGenerateInvokesCallback(): void
    {
        $transport = new HttpClientStub();
        $transport->response['body'] = json_encode([
            'content' => [
                ['type' => 'text', 'text' => 'Generated response'],
            ],
        ]);

        $seen = null;
        $client = new ClaudeClient('key', 'https://api.anthropic.test', $transport);
        $response = $client->generate('Prompt', [], function (array $result) use (&$seen): void {
            $seen = $result;
        });

        $this->assertSame('Generated response', $response['completion']);
        $this->assertSame($response, $seen);
    }
}
