<?php

namespace BlueFission\SimpleClients\Contracts;

use BlueFission\Arr;
use BlueFission\Str;
use BlueFission\Val;

class OptionalDependency extends ContractObject
{
    protected function memberDefaults(): array
    {
        return [
            'package' => '',
            'class' => '',
            'capability' => '',
            'install_hint' => '',
        ];
    }

    protected function memberConstraints(): array
    {
        return [
            'package' => $this->stringConstraint(),
            'class' => $this->stringConstraint(),
            'capability' => $this->stringConstraint(),
            'install_hint' => $this->stringConstraint(),
        ];
    }

    public static function forClass(string $package, string $class, string $capability, string $installHint = ''): self
    {
        return new self([
            'package' => $package,
            'class' => $class,
            'capability' => $capability,
            'install_hint' => $installHint,
        ]);
    }

    public function package(): string
    {
        return $this->stringMember('package');
    }

    public function class(): string
    {
        return $this->stringMember('class');
    }

    public function capability(): string
    {
        return $this->stringMember('capability');
    }

    public function installHint(): string
    {
        return $this->stringMember('install_hint');
    }

    public function available(): bool
    {
        return Val::isNotEmpty($this->class()) && class_exists($this->class());
    }

    public function message(): string
    {
        if ($this->available()) {
            return '';
        }

        $parts = Arr::make([
            $this->capability(),
            'requires optional package',
            $this->package(),
        ]);

        if (Str::isNotEmpty($this->installHint())) {
            $parts->push($this->installHint());
        }

        return $parts->join(': ')->val();
    }

    public function toArray(): array
    {
        return [
            'package' => $this->package(),
            'class' => $this->class(),
            'capability' => $this->capability(),
            'install_hint' => $this->installHint(),
            'available' => $this->available(),
        ];
    }
}
