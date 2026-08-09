<?php

namespace BlueFission\SimpleClients;

use BlueFission\Func;
use BlueFission\Str;
use BlueFission\Val;

class Runtime
{
    public static function env(string $key, string $fallback = ''): string
    {
        if (self::isCallable('env')) {
            $value = env($key);

            return Val::isNotEmpty($value) ? (string)$value : $fallback;
        }

        $value = getenv($key);

        return $value === false ? $fallback : (string)$value;
    }

    public static function classAvailable(string $class): bool
    {
        return Val::isNotEmpty($class) && class_exists($class);
    }

    public static function isCallable(mixed $callable): bool
    {
        return Func::isCallable($callable);
    }

    public static function canCall(object|string $target, string $method): bool
    {
        return self::isCallable([$target, $method]);
    }

    public static function objectCanCall(mixed $target, string $method): bool
    {
        return self::isCallable([$target, $method]);
    }
}
