<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\GrokClient;
use BlueFission\SimpleClients\Tests\Support\HttpClientStub;
use PHPUnit\Framework\TestCase;

class GrokClientTest extends TestCase
{
    public function testCompleteCallsChatCompletionsApiWithInjectedTransport(): void
    {
        $transport = new HttpClientStub();
        $transport->response['body'] = json_encode([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => 'Grok fixture answer',
                    ],
                ],
            ],
        ]);

        $client = new GrokClient('key', 'https://api.x.test', $transport);
        $response = $client->complete('Sample prompt', [
            'model' => 'grok-test',
            'temperature' => 0.2,
        ]);

        $this->assertSame('Grok fixture answer', $response['message']);
        $this->assertSame(200, $response['status']);
        $this->assertSame('', $response['error']);
        $this->assertSame('POST', $transport->calls[0]['method']);
        $this->assertSame('https://api.x.test/v1/chat/completions', $transport->calls[0]['url']);
        $this->assertSame('Bearer key', $transport->calls[0]['headers']['Authorization']);

        $payload = json_decode($transport->calls[0]['body'], true);
        $this->assertSame('grok-test', $payload['model']);
        $this->assertSame(0.2, $payload['temperature']);
        $this->assertSame('Sample prompt', $payload['messages'][0]['content']);
    }

    public function testRespondAliasesComplete(): void
    {
        $transport = new HttpClientStub();
        $transport->response['body'] = json_encode([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Chat response',
                    ],
                ],
            ],
        ]);

        $client = new GrokClient('key', 'https://api.x.test', $transport);
        $response = $client->respond('Chat here');

        $this->assertSame('Chat response', $response['message']);
    }

    public function testErrorResponsesAreReturnedWithoutThrowing(): void
    {
        $transport = new HttpClientStub();
        $transport->response = [
            'status' => 401,
            'body' => json_encode([
                'error' => ['message' => 'invalid api key'],
            ]),
            'headers' => [],
        ];

        $client = new GrokClient('key', 'https://api.x.test', $transport);
        $response = $client->complete('Prompt');

        $this->assertSame(401, $response['status']);
        $this->assertSame('invalid api key', $response['error']);
        $this->assertSame('', $response['message']);
    }
}
