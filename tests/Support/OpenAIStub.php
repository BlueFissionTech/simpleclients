<?php

declare(strict_types=1);

namespace BlueFission\Automata\LLM\Connectors;

class OpenAI
{
    public function __construct(string $apiKey = '')
    {
    }

    public function generate($input, $config = [], ?callable $callback = null)
    {
    }

    public function complete($input, $config = [])
    {
    }

    public function chat($input, $config = [])
    {
    }

    public function embeddings($input)
    {
    }
}
