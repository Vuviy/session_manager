<?php

namespace App\Database;

use Exception;
use PDO;

final class Database
{
    private string $driver;
    private ?PDO $connection = null;
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->driver = $config['default'];
    }

    private function connect(): PDO
    {
        if ($this->connection) {
            return $this->connection;
        }

        $settings = $this->config['connections'][$this->driver];

        switch ($settings['driver']) {
            case 'sqlite':
                $this->connection = new PDO("sqlite:" . $settings['database']);
                break;
            case 'mysql':
                $this->connection = new PDO(
                    "mysql:host={$settings['host']};dbname={$settings['dbname']}",
                    $settings['user'],
                    $settings['password']
                );
                break;
            case 'pgsql':
                $this->connection = new PDO(
                    "pgsql:host={$settings['host']};dbname={$settings['dbname']}",
                    $settings['user'],
                    $settings['password']
                );
                break;
            default:
                throw new Exception("Unsupported driver: {$settings['driver']}");
        }

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $this->connection;
    }

    public function beginTransaction(): void
    {
        $this->connect()->beginTransaction();
    }

    public function commit(): void
    {
        $this->connect()->commit();
    }

    public function rollback(): void
    {
        $this->connect()->rollBack();
    }

    public function select(string $sql, array $params = []): array
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insert(string $sql, array $params = []): int
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return (int)$this->connect()->lastInsertId();
    }

    public function update(string $sql, array $params = []): int
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function delete(string $sql, array $params = []): int
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }
}
