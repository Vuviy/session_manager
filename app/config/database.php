<?php
declare(strict_types=1);

return [
    'default' => 'mysql',

    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => __DIR__ . '/../storage/database.sqlite',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => getenv('DB_HOST'),
            'dbname' => getenv('DB_NAME'),
            'user' => getenv('USER_NAME'),
            'password' => getenv('PASSWORD'),
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => getenv('DB_HOST'),
            'dbname' => getenv('DB_NAME'),
            'user' => getenv('USER_NAME'),
            'password' => getenv('PASSWORD'),
        ],
    ],
];