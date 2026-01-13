<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

if (!class_exists(\BlueFission\Automata\LLM\Connectors\OpenAI::class)) {
    require __DIR__ . '/Support/OpenAIStub.php';
}

require_once __DIR__ . '/Support/GeminiStubs.php';
