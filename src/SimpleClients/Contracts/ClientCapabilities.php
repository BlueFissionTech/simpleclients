<?php

namespace BlueFission\SimpleClients\Contracts;

use BlueFission\Arr;
use BlueFission\Obj;

class ClientCapabilities extends Obj
{
    public function __construct(array $values = [])
    {
        parent::__construct();

        $actions = $values['actions'] ?? [];
        $auth = $values['auth'] ?? [];
        $transports = $values['transports'] ?? ['http'];
        $retry = $values['retry'] ?? [];
        $config = $values['config'] ?? [];

        $this->_data['service'] = (string)($values['service'] ?? '');
        $this->_data['actions'] = Arr::is($actions) ? $actions : [];
        $this->_data['auth'] = Arr::is($auth) ? $auth : [];
        $this->_data['transports'] = Arr::is($transports) ? $transports : ['http'];
        $this->_data['retry'] = Arr::is($retry) ? $retry : [];
        $this->_data['config'] = Arr::is($config) ? $config : [];
    }

    public function service(): string
    {
        return (string)($this->_data['service'] ?? '');
    }

    public function actions(): array
    {
        return Arr::is($this->_data['actions'] ?? []) ? $this->_data['actions'] : [];
    }

    public function auth(): array
    {
        return Arr::is($this->_data['auth'] ?? []) ? $this->_data['auth'] : [];
    }

    public function transports(): array
    {
        return Arr::is($this->_data['transports'] ?? []) ? $this->_data['transports'] : [];
    }

    public function retry(): array
    {
        return Arr::is($this->_data['retry'] ?? []) ? $this->_data['retry'] : [];
    }

    public function config(): array
    {
        return Arr::is($this->_data['config'] ?? []) ? $this->_data['config'] : [];
    }

    public function toArray(): array
    {
        return [
            'service' => $this->service(),
            'actions' => $this->actions(),
            'auth' => $this->auth(),
            'transports' => $this->transports(),
            'retry' => $this->retry(),
            'config' => $this->config(),
        ];
    }
}
