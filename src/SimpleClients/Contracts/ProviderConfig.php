<?php

namespace BlueFission\SimpleClients\Contracts;

class ProviderConfig extends ContractObject
{
    protected function memberDefaults(): array
    {
        return [
            'provider' => '',
            'endpoint' => '',
            'auth' => [],
            'headers' => [],
            'query' => [],
            'options' => [],
        ];
    }

    protected function memberConstraints(): array
    {
        return [
            'provider' => $this->stringConstraint(),
            'endpoint' => $this->stringConstraint(),
            'auth' => $this->arrayConstraint(),
            'headers' => $this->arrayConstraint(),
            'query' => $this->arrayConstraint(),
            'options' => $this->arrayConstraint(),
        ];
    }

    public function provider(): string
    {
        return $this->stringMember('provider');
    }

    public function endpoint(): string
    {
        return $this->stringMember('endpoint');
    }

    public function auth(): array
    {
        return $this->arrayMember('auth');
    }

    public function headers(): array
    {
        return $this->arrayMember('headers');
    }

    public function query(): array
    {
        return $this->arrayMember('query');
    }

    public function options(): array
    {
        return $this->arrayMember('options');
    }

    public function toArray(): array
    {
        return [
            'provider' => $this->provider(),
            'endpoint' => $this->endpoint(),
            'auth' => $this->auth(),
            'headers' => $this->headers(),
            'query' => $this->query(),
            'options' => $this->options(),
        ];
    }
}
