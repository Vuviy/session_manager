<?php

declare(strict_types=1);

use App\Database\ConnectionFactory;
use App\Database\Database;
use App\Database\Drivers\MySqlDriver;
use App\Database\Drivers\PgSqlDriver;
use App\Database\Drivers\SqliteDriver;
use App\Database\QueryExecutor;
use App\Repository\SessionRepository;
use App\SessionCrypto;
use App\SessionFingerprint;
use App\SessionManager;

$config = config();
$factory = new ConnectionFactory();

$drivers =[
    new MySqlDriver(),
    new PgSqlDriver(),
    new SqliteDriver(),
];

$connectionsConfig = $config['connections'];

foreach ($drivers as $driver) {
    if(array_key_exists($driver->getName(), $connectionsConfig)) {
        $factory->register($driver);
    }
}

$driverName = $config['connections'][$config['default']]['driver'];
$settings = $config['connections'][$config['default']];

$driver = $factory->create($driverName, $settings);

$executor = new QueryExecutor($driver);
$repo = new SessionRepository(new Database($executor));
$fingerprint = new SessionFingerprint();
$crypto = new SessionCrypto(cypherKey());
$session = new SessionManager($repo, $fingerprint, $crypto);




