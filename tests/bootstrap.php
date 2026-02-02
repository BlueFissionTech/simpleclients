<?php

declare(strict_types=1);

namespace {
    require __DIR__ . '/../vendor/autoload.php';

    if (!class_exists(\BlueFission\Automata\LLM\Connectors\OpenAI::class)) {
        require __DIR__ . '/Support/OpenAIStub.php';
    }

    require_once __DIR__ . '/Support/GeminiStubs.php';
    require_once __DIR__ . '/Support/HttpFixtures.php';
    require_once __DIR__ . '/Support/CurlStub.php';
    require_once __DIR__ . '/Support/HtmlWebStub.php';
    require_once __DIR__ . '/Support/HttpClientStub.php';

    if (!function_exists('env')) {
        function env(string $key): string
        {
            $value = getenv($key);
            return $value === false ? '' : (string)$value;
        }
    }
}

namespace BlueFission\SimpleClients {
    use BlueFission\SimpleClients\Tests\Support\HttpFixtures;

    if (!function_exists(__NAMESPACE__ . '\\file_get_contents')) {
        function file_get_contents(string $url, bool $use_include_path = false, $context = null, int $offset = 0, ?int $length = null): string
        {
            return HttpFixtures::get($url);
        }
    }

    if (!function_exists(__NAMESPACE__ . '\\stream_context_create')) {
        function stream_context_create(array $options = [])
        {
            return \stream_context_create($options);
        }
    }
}
