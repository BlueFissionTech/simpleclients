<?php

namespace BlueFission\SimpleClients\Contracts;

use BlueFission\Arr;
use BlueFission\Obj;
use BlueFission\Str;

class ClientRequest extends Obj
{
    public function __construct(array $values = [])
    {
        parent::__construct();

        $headers = $values['headers'] ?? [];
        $query = $values['query'] ?? [];
        $options = $values['options'] ?? [];

        $this->_data['method'] = Str::upper((string)($values['method'] ?? 'GET'));
        $this->_data['url'] = (string)($values['url'] ?? '');
        $this->_data['headers'] = Arr::is($headers) ? $headers : [];
        $this->_data['query'] = Arr::is($query) ? $query : [];
        $this->_data['body'] = $values['body'] ?? null;
        $this->_data['options'] = Arr::is($options) ? $options : [];
    }

    public function method(): string
    {
        return (string)($this->_data['method'] ?? 'GET');
    }

    public function url(): string
    {
        return (string)($this->_data['url'] ?? '');
    }

    public function headers(): array
    {
        return Arr::is($this->_data['headers'] ?? []) ? $this->_data['headers'] : [];
    }

    public function query(): array
    {
        return Arr::is($this->_data['query'] ?? []) ? $this->_data['query'] : [];
    }

    public function body()
    {
        return $this->_data['body'] ?? null;
    }

    public function options(): array
    {
        return Arr::is($this->_data['options'] ?? []) ? $this->_data['options'] : [];
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
