<?php
declare(strict_types=1);

namespace App\Database\Drivers;

use App\Database\Contracts\DriverInterface;
use PDO;

class PgSqlDriver implements DriverInterface
{
    public function getName(): string
    {
        return 'pgsql';
    }

    public function buildDsn(array $config): string
    {
        return sprintf('pgsql:host=%s;dbname=%s', $config['host'], $config['dbname']);

    }

    public function getDefaultOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => false,
        ];
    }
}