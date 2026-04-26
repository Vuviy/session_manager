<?php

declare(strict_types=1);

use App\Controller\AuthController;
use App\Controller\Controller;


require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();

require __DIR__ . '/functions/functions.php';
require __DIR__ . '/bootstrap.php';


if ($_SERVER['REQUEST_URI'] === '/favicon.ico') {
    return;
}

if ($_SERVER['REQUEST_URI'] === '/') {
    $cont = new Controller($session);

    $cont->home();
}

if ($_SERVER['REQUEST_URI'] === '/login') {
    $cont = new AuthController($session);

    $cont->login();
}




