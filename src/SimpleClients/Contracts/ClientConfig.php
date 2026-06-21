<?php

namespace BlueFission\SimpleClients\Contracts;

use BlueFission\Arr;
use BlueFission\Obj;

class ClientConfig extends Obj
{
    public function __construct(array $values = [])
    {
        parent::__construct();

        $auth = $values['auth'] ?? [];
        $headers = $values['headers'] ?? [];
        $options = $values['options'] ?? [];

        $this->_data['auth'] = Arr::is($auth) ? $auth : [];
        $this->_data['base_url'] = (string)($values['base_url'] ?? '');
        $this->_data['headers'] = Arr::is($headers) ? $headers : [];
        $this->_data['options'] = Arr::is($options) ? $options : [];
    }

    public function auth(): array
    {
        return Arr::is($this->_data['auth'] ?? []) ? $this->_data['auth'] : [];
    }

    public function baseUrl(): string
    {
        return (string)($this->_data['base_url'] ?? '');
    }

    public function headers(): array
    {
        return Arr::is($this->_data['headers'] ?? []) ? $this->_data['headers'] : [];
    }

    public function options(): array
    {
        return Arr::is($this->_data['options'] ?? []) ? $this->_data['options'] : [];
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
