<?php

namespace LinkORB\Component\Hatter\Model;

use Symfony\Component\Uid\Uuid;
use Xuid\Xuid;

class Column
{
    private $name;
    private $type;
    private $generator;
    private $autoIncrementCounter = 0;

    public static function fromArray(string $name, array $config): self
    {
        $column = new self();
        $column->name = $name;
        $column->type = $config['type'] ?? null;
        $column->generator = $config['generator'] ?? null;

        return $column;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getGenerator(): ?string
    {
        return $this->generator;
    }

    public function getGeneratedValue(): mixed
    {
        switch ($this->generator) {
            case 'autoIncrement':
                $this->autoIncrementCounter++;
                return $this->autoIncrementCounter;
            case 'uuid.v4':
                return (string)Uuid::v4();
            case 'xuid':
                return Xuid::getXuid();
            default:
                throw new \InvalidArgumentException('Unknown generator: ' . $this->generator);
        }
    }
    
}