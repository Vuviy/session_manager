<?php

declare(strict_types=1);

namespace App\Database;

use App\Database\Contracts\DriverInterface;
use InvalidArgumentException;
use PDO;

class ConnectionFactory
{
    private array $drivers = [];

    public function register(DriverInterface $driver): void
    {
        $this->drivers[$driver->getName()] = $driver;
    }

    public function create(string $driverName, array $config): PDO
    {
        if (!array_key_exists($driverName, $this->drivers)) {
            throw new InvalidArgumentException(
                sprintf('Unsupported driver: %s', $driverName)
            );
        }

        $driver = $this->drivers[$driverName];

        return new PDO(
            $driver->buildDsn($config),
            $config['user'] ?? null,
            $config['password'] ?? null,
            $driver->getDefaultOptions()
        );
    }
}