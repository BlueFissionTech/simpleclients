<?php

namespace BlueFission\SimpleClients\Contracts;

class ClientCapabilities extends ContractObject
{
    protected function memberDefaults(): array
    {
        return [
            'service' => '',
            'actions' => [],
            'auth' => [],
            'transports' => ['http'],
            'retry' => [],
            'config' => [],
        ];
    }

    protected function memberConstraints(): array
    {
        return [
            'service' => $this->stringConstraint(),
            'actions' => $this->arrayConstraint(),
            'auth' => $this->arrayConstraint(),
            'transports' => $this->arrayConstraint(['http']),
            'retry' => $this->arrayConstraint(),
            'config' => $this->arrayConstraint(),
        ];
    }

    public function service(): string
    {
        return $this->stringMember('service');
    }

    public function actions(): array
    {
        return $this->arrayMember('actions');
    }

    public function auth(): array
    {
        return $this->arrayMember('auth');
    }

    public function transports(): array
    {
        return $this->arrayMember('transports');
    }

    public function retry(): array
    {
        return $this->arrayMember('retry');
    }

    public function config(): array
    {
        return $this->arrayMember('config');
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
