<?php

function config()
{
    return require __DIR__ . '/../config/database.php';
}

function cypherKey()
{
    $key = getenv('CIPHER_KEY');

    if (!$key) {
        throw new \RuntimeException('CIPHER_KEY is not set in environment');
    }

    return $key;
}