<?php

namespace LinkORB\Component\Hatter\Model;

class Table
{
    private $name;
    private $rows = [];
    private $columns = [];

    public static function fromArray(string $name, array $config): self
    {
        $table = new self();
        $table->name = $name;
        foreach ($config['columns'] ?? [] as $columnName => $columnData) {
            $column = Column::fromArray($columnName, $columnData);
            $table->addColumn($column);
        }
        foreach ($config['rows'] ?? [] as $rowKey => $rowData) {
            $row = Row::fromArray($rowKey, $rowData);
            $table->addRow($row);
        }

        return $table;
    }

    public function addRow(Row $row)
    {
        $this->rows[$row->getKey()] = $row;
    }

    public function getRow(string $key): Row
    {
        if (!$this->hasRow($key)) {
            throw new \InvalidArgumentException('Row not found: ' . $key);
        }
        return $this->rows[$key];
    }

    public function hasRow(string $key): bool
    {
        return isset($this->rows[$key]);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRows(): array
    {
        return $this->rows;
    }

    public function addColumn(Column $column)
    {
        $this->columns[$column->getName()] = $column;
    }

    public function getColumn(string $name): Column
    {
        if (!$this->hasColumn($name)) {
            throw new \InvalidArgumentException('Column not found: ' . $name);
        }
        return $this->columns[$name];
    }

    public function hasColumn(string $name): bool
    {
        return isset($this->columns[$name]);
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

}