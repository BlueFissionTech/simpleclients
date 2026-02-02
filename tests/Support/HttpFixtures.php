<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests\Support;

class HttpFixtures
{
    private static array $responses = [];

    public static function set(string $url, string $body): void
    {
        self::$responses[$url] = $body;
    }

    public static function get(string $url): string
    {
        return self::$responses[$url] ?? '';
    }

    public static function clear(): void
    {
        self::$responses = [];
    }
}
