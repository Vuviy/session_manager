<?php

declare(strict_types=1);

namespace App\Database;

final class Database
{
    public function __construct(private QueryExecutor $executor) {}

    public function select(string $sql, array $params = []): array
    {
        return $this->executor->select($sql, $params);
    }

    public function insert(string $sql, array $params = []): int
    {
        return $this->executor->insert($sql, $params);
    }

    public function update(string $sql, array $params = []): int
    {
        return $this->executor->update($sql, $params);
    }

    public function delete(string $sql, array $params = []): int
    {
        return $this->executor->delete($sql, $params);
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }
}
