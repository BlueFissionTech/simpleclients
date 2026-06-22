<?php

namespace BlueFission\SimpleClients\Contracts;

use BlueFission\Arr;
use BlueFission\Behavioral\Behaviors\State;
use BlueFission\Obj;
use BlueFission\Str;

abstract class ContractObject extends Obj
{
    private array $memberConstraints = [];

    public function __construct(array $values = [])
    {
        parent::__construct();

        $this->memberConstraints = $this->memberConstraints();

        $this->perform(State::CHANGING);
        foreach ($this->memberDefaults() as $field => $default) {
            $this->setContractField($field, $values[$field] ?? $default);
        }
        $this->halt(State::CHANGING);
    }

    protected function memberDefaults(): array
    {
        return [];
    }

    protected function memberConstraints(): array
    {
        return [];
    }

    protected function arrayMember(string $field, array $fallback = []): array
    {
        $value = $this->_data[$field] ?? $fallback;

        return Arr::is($value) ? $value : $fallback;
    }

    protected function stringMember(string $field, string $fallback = ''): string
    {
        return (string)($this->_data[$field] ?? $fallback);
    }

    protected function intMember(string $field, int $fallback = 0): int
    {
        return (int)($this->_data[$field] ?? $fallback);
    }

    protected function arrayConstraint(array $fallback = []): callable
    {
        return static fn ($value): array => Arr::is($value) ? $value : $fallback;
    }

    protected function stringConstraint(): callable
    {
        return static fn ($value): string => (string)$value;
    }

    protected function upperStringConstraint(string $fallback = ''): callable
    {
        return static fn ($value): string => Str::upper((string)($value ?? $fallback));
    }

    protected function intConstraint(): callable
    {
        return static fn ($value): int => (int)$value;
    }

    private function setContractField(string $field, mixed $value): void
    {
        $constraint = $this->memberConstraints[$field] ?? null;

        if (is_callable($constraint)) {
            $value = $constraint($value);
        }

        $this->_data[$field] = $value;
    }
}
