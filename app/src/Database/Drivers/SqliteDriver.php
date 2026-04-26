<?php
declare(strict_types=1);

namespace App\Database\Drivers;

use App\Database\Contracts\DriverInterface;
use PDO;

class SqliteDriver implements DriverInterface
{
    public function getName(): string
    {
        return 'sqlite';
    }

    public function buildDsn(array $config): string
    {
        return sprintf('sqlite:%s', $config['database']);
    }

    public function getDefaultOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];
    }
}