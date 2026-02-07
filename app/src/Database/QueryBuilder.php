<?php

namespace App\Database;

use Exception;

final class QueryBuilder
{
    private Database $db;
    private string $table;
    private array $wheres = [];
    private array $bindings = [];
    private ?string $orderBy = null;
    private ?int $limit = null;
    private array $joins = [];
    private array $groups = [];
    private array $havings = [];

    public function __construct(Database $db, string $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function where(string $column, string $operator, $value): self
    {
        if ($value instanceof Raw) {
            $this->wheres[] = "$column $operator {$value->value}";
            return $this;
        }

        $placeholder = ':' . str_replace('.', '_', $column) . count($this->bindings);
        $this->wheres[] = "$column $operator $placeholder";
        $this->bindings[$placeholder] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $this->orderBy = "$column $direction";
        return $this;
    }


    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function get(array $columns = ['*']): array
    {
        $sql = "SELECT " . implode(', ', $columns) . " FROM {$this->table}";
        if ($this->joins) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if ($this->wheres) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        if ($this->groups) {
            $sql .= " GROUP BY " . implode(', ', $this->groups);
        }

        if ($this->havings) {
            $sql .= " HAVING " . implode(' AND ', $this->havings);
        }

        if ($this->orderBy) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        return $this->db->select($sql, $this->bindings);
    }

    public function first(array $columns = ['*']): ?array
    {
        $this->limit(1);
        $results = $this->get($columns);
        return $results[0] ?? null;
    }

    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ':' . $col, $columns);

        $sql = "INSERT INTO {$this->table} (" . implode(',', $columns) . ") VALUES (" . implode(',', $placeholders) . ")";
        $bindings = [];
        foreach ($data as $col => $val) {
            $bindings[':' . $col] = $val;
        }

        return $this->db->insert($sql, $bindings);
    }

    public function update(array $data): int
    {
        if (!$this->wheres) {
            throw new Exception("Update without WHERE is not allowed!");
        }

        $set = [];
        $bindings = $this->bindings;

        foreach ($data as $col => $val) {
            $placeholder = ':' . $col . '_upd';
            $set[] = "$col = $placeholder";
            $bindings[$placeholder] = $val;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . " WHERE " . implode(' AND ', $this->wheres);
        return $this->db->update($sql, $bindings);
    }

    public function delete(): int
    {
        if (!$this->wheres) {
            throw new Exception("Delete without WHERE is not allowed!");
        }

        $sql = "DELETE FROM {$this->table} WHERE " . implode(' AND ', $this->wheres);
        return $this->db->delete($sql, $this->bindings);
    }

    public function reset(): self
    {
        $this->wheres = [];
        $this->bindings = [];
        $this->orderBy = null;
        $this->limit = null;
        return $this;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $this->joins[] = strtoupper($type) . " JOIN $table ON $first $operator $second";
        return $this;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    public function crossJoin(string $table): self
    {
        $this->joins[] = "CROSS JOIN $table";
        return $this;
    }

    public function whereIn(string $column, QueryBuilder $sub): self
    {
        [$sql, $bindings] = $sub->toSubquery();

        $this->wheres[] = "$column IN ($sql)";
        $this->bindings = array_merge($this->bindings, $bindings);

        return $this;
    }

    public function whereExists(QueryBuilder $sub): self
    {
        [$sql, $bindings] = $sub->toSubquery();

        $this->wheres[] = "EXISTS ($sql)";
        $this->bindings = array_merge($this->bindings, $bindings);

        return $this;
    }

    private function toSubquery(): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if ($this->wheres) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        return [$sql, $this->bindings];
    }

    private function aggregate(string $function, string $column)
    {
        $sql = "SELECT $function($column) as aggregate FROM {$this->table}";

        if ($this->joins) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if ($this->wheres) {
            $sql .= " WHERE " . implode(' AND ', $this->wheres);
        }

        return $this->db->select($sql, $this->bindings)[0]['aggregate'];
    }

    public function count(string $column = '*'): int
    {
        return (int)$this->aggregate('COUNT', $column);
    }

    public function sum(string $column): float
    {
        return (float)$this->aggregate('SUM', $column);
    }

    public function avg(string $column): float
    {
        return (float)$this->aggregate('AVG', $column);
    }

    public function min(string $column)
    {
        return $this->aggregate('MIN', $column);
    }

    public function max(string $column)
    {
        return $this->aggregate('MAX', $column);
    }

    public function groupBy(string ...$columns): self
    {
        $this->groups = $columns;
        return $this;
    }

    public function having(string $column, string $operator, $value): self
    {
        $ph = ':having_' . count($this->bindings);
        $this->havings[] = "$column $operator $ph";
        $this->bindings[$ph] = $value;
        return $this;
    }
}
