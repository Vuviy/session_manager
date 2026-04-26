<?php
declare(strict_types=1);

namespace App\Database\Drivers;

use App\Database\Contracts\DriverInterface;
use PDO;

class MySqlDriver implements DriverInterface
{
    public function getName(): string
    {
        return 'mysql';
    }

    public function buildDsn(array $config): string
    {
        return sprintf('mysql:host=%s;dbname=%s', $config['host'], $config['dbname']);
    }

    public function getDefaultOptions(): array
    {
        return [
            PDO::ATTR_ERRMODE    => PDO::ERRMODE_EXCEPTION,
        ];
    }
}