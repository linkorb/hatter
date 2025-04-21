<?php

namespace LinkORB\Component\Hatter;

use ArrayAccess;
use Faker\Factory as FakerFactory;
use LinkORB\Component\Hatter\Model\Column;
use LinkORB\Component\Hatter\Model\Table;
use PDO;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class Hatter implements ArrayAccess
{
    private array $tables = [];

    public static function fromArray(array $config): self
    {
        $hatter = new self();
        foreach ($config['tables'] as $tableName => $tableConfig) {
            $table = Table::fromArray($tableName, $tableConfig);
            $hatter->addTable($table);
        }

        $hatter->postProcess();
        return $hatter;
    }

    public function postProcess(): void
    {
        $expressionLanguage = new ExpressionLanguage();

        $faker = FakerFactory::create();
        $faker->seed();

        // auto detect columns from rows
        foreach ($this->tables as $table) {
            foreach ($table->getRows() as $row) {
                foreach ($row->getValues() as $columnName => $value) {
                    if (!$table->hasColumn($columnName)) {
                        $column = Column::fromArray($columnName, []);
                        $table->addColumn($column);
                    }
                }
            }
        }

        // apply generated column values
        foreach ($this->tables as $table) {
            foreach ($table->getRows() as $row) {
                foreach (array_reverse($table->getColumns()) as $column) {
                    if ($column->getGenerator()) {
                        if (!$row->getValue($column->getName())) {
                            $value = $column->getGeneratedValue();
                            $row->setValue($column->getName(), $value, true);
                        }
                    }
                }
            }
        }

        // apply expressions and references
        foreach ($this->tables as $table) {
            foreach ($table->getRows() as $row) {
                foreach ($row->getValues() as $key => $value) {
                    if (preg_match('/\{\{(.*)\}\}/', $value, $matches)) {
                        $expression = trim($matches[1]);
                        $values = [
                            'faker' => $faker,
                            'hatter' => $this,
                        ];
                        $value = $expressionLanguage->evaluate($expression, $values);
                        $row->setValue($key, $value);
                    }

                    // match values like `@user.alice.id` capturing `user`, `alice` and `id`
                    if (preg_match('/@([a-z0-9_-]+)\.([a-z0-9_-]+)\.([a-z0-9_-]+)/', $value, $matches)) {
                        $refTable = $this->getTable($matches[1]);
                        $refRow = $refTable->getRow($matches[2]);
                        $value = $refRow->getValue($matches[3]);
                        $row->setValue($key, $value);
                    }

                }
            }
        }
    }

    public function addTable(Table $table)
    {
        $this->tables[$table->getName()] = $table;
    }

    public function getTable(string $name): Table
    {
        return $this->tables[$name];
    }

    public function getTables(): array
    {
        return $this->tables;
    }

    public function offsetExists($offset): bool
    {
        return $offset == 'tables';
    }

    public function offsetGet($offset): mixed
    {
        if ($offset == 'tables') {
            return $this->getTables();
        }
        throw new \Exception('No such hatter property: ' . $offset);
    }

    public function offsetSet($offset, $value): void
    {
        throw new \Exception('Not implemented');
    }

    public function offsetUnset($offset): void
    {
        throw new \Exception('Not implemented');
    }

    public function serialize(): array
    {
        $config = [
            'tables' => [],
        ];
        foreach ($this->getTables() as $table) {
            $config['tables'][$table->getName()] = [];
            foreach ($table->getColumns() as $column) {
                $config['tables'][$table->getName()]['columns'][$column->getName()] = [
                    'type' => $column->getType(),
                    'generator' => $column->getGenerator(),
                ];
            }

            foreach ($table->getRows() as $row) {
                $config['tables'][$table->getName()]['rows'][$row->getKey()] = $row->getValues();
            }
        }
        return $config;
    }

    public function write(PDO $pdo, bool $skip_foreign_key_checks = false): void
    {
        $driver = strtolower($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
    
        switch ($driver) {
            case 'sqlite':
                if ($skip_foreign_key_checks) {
                    $pdo->exec('PRAGMA foreign_keys = OFF');
                }
                break;
            case 'mysql':
            case 'mariadb':
                if ($skip_foreign_key_checks) {
                    $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
                }
                break;
            default:
                throw new \RuntimeException("Unsupported PDO driver: $driver");
        }
    
        foreach ($this->getTables() as $table) {
            $tableName = $table->getName();
    
            // defensively ensure table name is safe
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $tableName)) {
                throw new \RuntimeException("Unsafe table name: $tableName");
            }
    
            $clearSql = match ($driver) {
                'sqlite' => "DELETE FROM $tableName;",
                default => "TRUNCATE $tableName;"
            };
    
            $pdo->exec($clearSql);
    
            foreach ($table->getRows() as $row) {
                $sql = 'INSERT INTO ' . $tableName . ' (';
                foreach ($table->getColumns() as $column) {
                    $sql .= $column->getName() . ', ';
                }
                $sql = rtrim($sql, ', ') . ') VALUES (';
                foreach ($table->getColumns() as $column) {
                    $value = $row->getValue($column->getName());
                    $sql .= is_null($value) ? 'NULL' : $pdo->quote($value);
                    $sql .= ', ';
                }
                $sql = rtrim($sql, ', ') . ');' . PHP_EOL;
    
                echo $sql . PHP_EOL;
    
                $stmt = $pdo->prepare($sql);
                $stmt->execute();
            }
        }
    
        switch ($driver) {
            case 'sqlite':
                if ($skip_foreign_key_checks) {
                    $pdo->exec('PRAGMA foreign_keys = ON');
                }
                break;
            case 'mysql':
            case 'mariadb':
                if ($skip_foreign_key_checks) {
                    $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
                }
                break;
        }
    }    
}
