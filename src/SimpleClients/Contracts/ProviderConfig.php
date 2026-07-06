<?php

namespace BlueFission\SimpleClients\Contracts;

use BlueFission\Arr;
use BlueFission\Val;

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

    public function config($config = null, $value = null): mixed
    {
        if (Val::isNull($config)) {
            return $this->toArray();
        }

        if (Arr::is($config)) {
            foreach ($config as $field => $fieldValue) {
                $this->field((string)$field, $fieldValue);
            }

            return $this;
        }

        if (func_num_args() > 1) {
            $this->field((string)$config, $value);

            return $this;
        }

        return $this->memberValue((string)$config);
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
