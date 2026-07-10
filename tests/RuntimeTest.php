<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests;

use BlueFission\SimpleClients\Runtime;
use PHPUnit\Framework\TestCase;

class RuntimeTest extends TestCase
{
    public function testEnvReadsProcessEnvironmentWithFallback(): void
    {
        putenv('SIMPLECLIENTS_RUNTIME_TEST=value');

        try {
            $this->assertSame('value', Runtime::env('SIMPLECLIENTS_RUNTIME_TEST'));
            $this->assertSame('fallback', Runtime::env('SIMPLECLIENTS_RUNTIME_MISSING', 'fallback'));
        } finally {
            putenv('SIMPLECLIENTS_RUNTIME_TEST');
        }
    }

    public function testRuntimeCapabilityChecksUseCallableHelpers(): void
    {
        $target = new class {
            public function prompt(): string
            {
                return 'ready';
            }
        };

        $this->assertTrue(Runtime::classAvailable(self::class));
        $this->assertFalse(Runtime::classAvailable('Vendor\\Missing\\Class'));
        $this->assertTrue(Runtime::canCall($target, 'prompt'));
        $this->assertTrue(Runtime::objectCanCall($target, 'prompt'));
        $this->assertFalse(Runtime::objectCanCall('not-object', 'prompt'));
    }
}
