<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\Automata\LLM\Connectors\OpenAI;
use BlueFission\SimpleClients\OpenAIClient;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class OpenAIClientTest extends TestCase
{
    private function clientWithMock(OpenAI $mock): OpenAIClient
    {
        $client = new OpenAIClient('test-key');

        $reflection = new ReflectionClass($client);
        $prop = $reflection->getProperty('_client');
        $prop->setAccessible(true);
        $prop->setValue($client, $mock);

        return $client;
    }

    public function testGenerateUsesConfigAndCallbackInOrder(): void
    {
        $mock = $this->createMock(OpenAI::class);
        $input = 'prompt text';
        $config = ['temperature' => 0.3, 'max_tokens' => 50];
        $callback = fn () => null;

        $mock->expects($this->once())
            ->method('generate')
            ->with($input, $config, $callback);

        $client = $this->clientWithMock($mock);

        $client->generate($input, $config, $callback);
    }

    public function testCompleteReturnsConnectorResponse(): void
    {
        $mock = $this->createMock(OpenAI::class);
        $input = 'summary';
        $config = ['top_p' => 0.7];
        $expected = ['choices' => [['text' => 'result']]];

        $mock->expects($this->once())
            ->method('complete')
            ->with($input, $config)
            ->willReturn($expected);

        $client = $this->clientWithMock($mock);

        $this->assertSame($expected, $client->complete($input, $config));
    }

    public function testChatPassesMessagesAndConfig(): void
    {
        $mock = $this->createMock(OpenAI::class);
        $messages = [['role' => 'user', 'content' => 'hello']];
        $config = ['max_tokens' => 10];
        $expected = ['choices' => [['message' => ['content' => 'hi']]]];

        $mock->expects($this->once())
            ->method('chat')
            ->with($messages, $config)
            ->willReturn($expected);

        $client = $this->clientWithMock($mock);

        $this->assertSame($expected, $client->chat($messages, $config));
    }

    public function testEmbeddingsReturnsConnectorResponse(): void
    {
        $mock = $this->createMock(OpenAI::class);
        $input = 'vectorize me';
        $expected = ['data' => [['embedding' => [1, 2, 3]]]];

        $mock->expects($this->once())
            ->method('embeddings')
            ->with($input)
            ->willReturn($expected);

        $client = $this->clientWithMock($mock);

        $this->assertSame($expected, $client->embeddings($input));
    }
}
