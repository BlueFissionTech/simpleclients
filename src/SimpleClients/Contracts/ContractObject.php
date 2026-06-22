<?php

namespace BlueFission\SimpleClients\Contracts;

use BlueFission\Arr;
use BlueFission\Behavioral\Behaviors\State;
use BlueFission\Func;
use BlueFission\IVal;
use BlueFission\Obj;
use BlueFission\Str;
use BlueFission\Val;

abstract class ContractObject extends Obj
{
    protected $_exposeValueObject = true;

    private array $memberConstraints = [];

    public function __construct(array $values = [])
    {
        parent::__construct();

        $this->perform(State::CHANGING);
        $this->memberConstraints = $this->memberConstraints();

        foreach ($this->memberDefaults() as $field => $default) {
            $this->prepareContractField($field, $default);

            if (array_key_exists($field, $values)) {
                $this->setContractField($field, $values[$field]);
            }
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
        $value = $this->memberValue($field, $fallback);

        return Arr::is($value) ? $value : $fallback;
    }

    protected function stringMember(string $field, string $fallback = ''): string
    {
        return (string)$this->memberValue($field, $fallback);
    }

    protected function intMember(string $field, int $fallback = 0): int
    {
        return (int)$this->memberValue($field, $fallback);
    }

    protected function memberValue(string $field, mixed $fallback = null): mixed
    {
        $value = $this->field($field);
        if ($value instanceof IVal) {
            $value = $value->val();
        }

        return $value ?? $fallback;
    }

    protected function arrayConstraint(array $fallback = []): callable
    {
        return static function (&$value) use ($fallback): void {
            $value = Arr::is($value) ? $value : $fallback;
        };
    }

    protected function stringConstraint(): callable
    {
        return static function (&$value): void {
            $value = (string)$value;
        };
    }

    protected function upperStringConstraint(string $fallback = ''): callable
    {
        return static function (&$value) use ($fallback): void {
            $value = Str::upper((string)($value ?? $fallback));
        };
    }

    protected function intConstraint(): callable
    {
        return static function (&$value): void {
            $value = (int)$value;
        };
    }

    private function prepareContractField(string $field, mixed $default): void
    {
        $member = new Val($default);
        $this->_data[$field] = $member;

        $constraint = $this->memberConstraints[$field] ?? null;

        if (Func::isCallable($constraint)) {
            $this->field($field)->constraint($constraint);
        }
    }

    private function setContractField(string $field, mixed $value): void
    {
        $this->field($field, $value);
    }
}
