<?php

namespace BlueFission\SimpleClients\Contracts;

class ClientRequest extends ContractObject
{
    protected function memberDefaults(): array
    {
        return [
            'method' => 'GET',
            'url' => '',
            'headers' => [],
            'query' => [],
            'body' => null,
            'options' => [],
        ];
    }

    protected function memberConstraints(): array
    {
        return [
            'method' => $this->upperStringConstraint('GET'),
            'url' => $this->stringConstraint(),
            'headers' => $this->arrayConstraint(),
            'query' => $this->arrayConstraint(),
            'options' => $this->arrayConstraint(),
        ];
    }

    public function method(): string
    {
        return $this->stringMember('method', 'GET');
    }

    public function url(): string
    {
        return $this->stringMember('url');
    }

    public function headers(): array
    {
        return $this->arrayMember('headers');
    }

    public function query(): array
    {
        return $this->arrayMember('query');
    }

    public function body()
    {
        return $this->_data['body'] ?? null;
    }

    public function options(): array
    {
        return $this->arrayMember('options');
    }

    public function toArray(): array
    {
        return [
            'method' => $this->method(),
            'url' => $this->url(),
            'headers' => $this->headers(),
            'query' => $this->query(),
            'body' => $this->body(),
            'options' => $this->options(),
        ];
    }
}
