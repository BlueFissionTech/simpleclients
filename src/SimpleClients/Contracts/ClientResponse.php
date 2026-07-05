<?php

namespace BlueFission\SimpleClients\Contracts;

use BlueFission\Arr;
use BlueFission\Val;

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

    public static function fromProviderResult(array $result, int $defaultStatus = 200, array $meta = []): self
    {
        $status = (int)Arr::make($result)->getPath('status', $defaultStatus);
        $error = (string)Arr::make($result)->getPath('error', '');

        if (Val::isNotEmpty($error) || $status >= 400) {
            return self::failure($error ?: 'Provider request failed.', $status, $meta);
        }

        return self::success($result, $status, $meta);
    }

    public static function fromHttpJson(array $data, int $status = 200, array $headers = [], array $meta = []): self
    {
        return new self([
            'status' => $status,
            'headers' => $headers,
            'data' => $data,
            'body' => $data,
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
        return $this->memberValue('body');
    }

    public function data(): mixed
    {
        return $this->memberValue('data');
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
