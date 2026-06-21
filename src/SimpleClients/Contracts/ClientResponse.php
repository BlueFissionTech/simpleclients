<?php

namespace BlueFission\SimpleClients\Contracts;

use BlueFission\Arr;
use BlueFission\Obj;

class ClientResponse extends Obj
{
    public function __construct(array $values = [])
    {
        parent::__construct();

        $headers = $values['headers'] ?? [];
        $meta = $values['meta'] ?? [];

        $this->_data['status'] = (int)($values['status'] ?? 0);
        $this->_data['headers'] = Arr::is($headers) ? $headers : [];
        $this->_data['body'] = $values['body'] ?? null;
        $this->_data['data'] = $values['data'] ?? null;
        $this->_data['error'] = (string)($values['error'] ?? '');
        $this->_data['meta'] = Arr::is($meta) ? $meta : [];
    }

    public static function success($data = null, int $status = 200, array $meta = []): self
    {
        return new self([
            'status' => $status,
            'data' => $data,
            'meta' => $meta,
        ]);
    }

    public static function failure(string $error, int $status = 0, array $meta = []): self
    {
        return new self([
            'status' => $status,
            'error' => $error,
            'meta' => $meta,
        ]);
    }

    public function ok(): bool
    {
        return $this->error() === '' && $this->status() >= 200 && $this->status() < 300;
    }

    public function status(): int
    {
        return (int)($this->_data['status'] ?? 0);
    }

    public function headers(): array
    {
        return Arr::is($this->_data['headers'] ?? []) ? $this->_data['headers'] : [];
    }

    public function body()
    {
        return $this->_data['body'] ?? null;
    }

    public function data(): mixed
    {
        return $this->_data['data'] ?? null;
    }

    public function error(): string
    {
        return (string)($this->_data['error'] ?? '');
    }

    public function meta(): array
    {
        return Arr::is($this->_data['meta'] ?? []) ? $this->_data['meta'] : [];
    }

    public function toArray(): array
    {
        return [
            'ok' => $this->ok(),
            'status' => $this->status(),
            'headers' => $this->headers(),
            'body' => $this->body(),
            'data' => $this->data(),
            'error' => $this->error(),
            'meta' => $this->meta(),
        ];
    }
}
