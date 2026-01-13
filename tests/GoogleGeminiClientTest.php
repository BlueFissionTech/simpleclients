<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\GoogleGeminiClient;
use BlueFission\SimpleClients\Tests\Support\GeminiClientStub;
use PHPUnit\Framework\TestCase;

class GoogleGeminiClientTest extends TestCase
{
    public function testGenerateUsesProvidedClient(): void
    {
        $clientStub = new GeminiClientStub();
        $client = new GoogleGeminiClient('key', $clientStub);

        $result = $client->generate('hello', ['top_p' => 0.5]);

        $this->assertSame('generated: hello', $result);
        $this->assertSame(['top_p' => 0.5], $clientStub->pro->generateContent('hello', ['top_p' => 0.5])->config());
    }

    public function testChatUsesHistoryAndConfig(): void
    {
        $clientStub = new GeminiClientStub();
        $client = new GoogleGeminiClient('key', $clientStub);
        $history = [['role' => 'user', 'content' => 'past']];

        $result = $client->chat('hi', ['max_tokens' => 10], $history);

        $this->assertSame('chat: hi', $result);
        $this->assertSame($history, $clientStub->pro->history);
        $this->assertSame([['input' => 'hi', 'config' => ['max_tokens' => 10]]], $clientStub->pro->sent);
    }

    public function testEmbeddingsReturnsValues(): void
    {
        $clientStub = new GeminiClientStub();
        $client = new GoogleGeminiClient('key', $clientStub);

        $embedding = $client->embeddings('text');

        $this->assertSame(['embedding' => ['values' => ['text-v1', 'text-v2']]], $embedding);
    }
}
