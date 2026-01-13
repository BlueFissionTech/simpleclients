<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests\Support;

class GeminiResponseStub
{
    public function __construct(private string $text, private array $config = [])
    {
    }

    public function text(): string
    {
        return $this->text;
    }

    public function config(): array
    {
        return $this->config;
    }
}

class GeminiProStub
{
    public array $history = [];
    public array $sent = [];

    public function generateContent($input, array $config = []): GeminiResponseStub
    {
        return new GeminiResponseStub("generated: {$input}", $config);
    }

    public function sendMessage($input, array $config = []): GeminiResponseStub
    {
        $this->sent[] = ['input' => $input, 'config' => $config];
        return new GeminiResponseStub("chat: {$input}", $config);
    }

    public function startChat(array $history = []): void
    {
        $this->history = $history;
    }
}

class GeminiEmbeddingStub
{
    public function embedContent($input): array
    {
        return ['embedding' => ['values' => ["{$input}-v1", "{$input}-v2"]]];
    }
}

class GeminiClientStub
{
    public GeminiProStub $pro;

    public function __construct()
    {
        $this->pro = new GeminiProStub();
    }

    public function geminiPro(): GeminiProStub
    {
        return $this->pro;
    }

    public function embeddingModel(): GeminiEmbeddingStub
    {
        return new GeminiEmbeddingStub();
    }
}
