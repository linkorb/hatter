<?php

namespace LinkORB\Component\Hatter\Model;

class Row
{
    private $key;
    private $values = [];

    public static function fromArray(string $key, array $config): self
    {
        $row = new self();
        $row->key = $key;
        $row->values = $config;

        return $row;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function getValue(string $key): mixed
    {
        return $this->values[$key] ?? null;
    }

    public function setValue(string $key, $value, $prepend = false): void
    {
        if ($prepend) {
            $this->values = [$key => $value] + $this->values;
            return;
        }
        $this->values[$key] = $value;
    }

}