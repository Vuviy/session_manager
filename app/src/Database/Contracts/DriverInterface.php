<?php
declare(strict_types=1);

namespace App\Database\Contracts;

interface DriverInterface
{
    public function getName(): string;
    public function buildDsn(array $config): string;
    public function getDefaultOptions(): array;
}