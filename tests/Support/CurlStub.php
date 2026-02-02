<?php

declare(strict_types=1);

namespace BlueFission\SimpleClients\Tests\Support;

class CurlStub
{
    public array $config = [];
    public array $queries = [];
    public string $result = '';

    public function config($key, $value = null): self
    {
        if (is_array($key)) {
            $this->config = array_merge($this->config, $key);
        } else {
            $this->config[$key] = $value;
        }
        return $this;
    }

    public function open(): self
    {
        return $this;
    }

    public function query($data = null): self
    {
        $this->queries[] = $data;
        return $this;
    }

    public function result(): string
    {
        return $this->result;
    }

    public function connection()
    {
        return null;
    }

    public function close(): self
    {
        return $this;
    }
}
