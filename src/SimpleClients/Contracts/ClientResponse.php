<?php

namespace BlueFission\SimpleClients\Contracts;

class ClientResponse extends ContractObject
{
    protected function memberDefaults(): array
    {
        return [
            'status' => 0,
            'headers' => [],
            'body' => null,
            'data' => null,
            'error' => '',
            'meta' => [],
        ];
    }

    protected function memberConstraints(): array
    {
        return [
            'status' => $this->intConstraint(),
            'headers' => $this->arrayConstraint(),
            'error' => $this->stringConstraint(),
            'meta' => $this->arrayConstraint(),
        ];
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
        return $this->intMember('status');
    }

    public function headers(): array
    {
        return $this->arrayMember('headers');
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
        return $this->stringMember('error');
    }

    public function meta(): array
    {
        return $this->arrayMember('meta');
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
