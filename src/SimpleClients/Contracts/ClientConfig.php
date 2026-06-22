<?php

namespace BlueFission\SimpleClients\Contracts;

class ClientConfig extends ContractObject
{
    protected function memberDefaults(): array
    {
        return [
            'auth' => [],
            'base_url' => '',
            'headers' => [],
            'options' => [],
        ];
    }

    protected function memberConstraints(): array
    {
        return [
            'auth' => $this->arrayConstraint(),
            'base_url' => $this->stringConstraint(),
            'headers' => $this->arrayConstraint(),
            'options' => $this->arrayConstraint(),
        ];
    }

    public function auth(): array
    {
        return $this->arrayMember('auth');
    }

    public function baseUrl(): string
    {
        return $this->stringMember('base_url');
    }

    public function headers(): array
    {
        return $this->arrayMember('headers');
    }

    public function options(): array
    {
        return $this->arrayMember('options');
    }

    public function toArray(): array
    {
        return [
            'auth' => $this->auth(),
            'base_url' => $this->baseUrl(),
            'headers' => $this->headers(),
            'options' => $this->options(),
        ];
    }
}
