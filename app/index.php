<?php

declare(strict_types=1);


require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions/functions.php';


//dd($_SERVER);


if ($_SERVER['REQUEST_URI'] === '/favicon.ico') {
    return;
}

if ($_SERVER['REQUEST_URI'] === '/') {
    $cont = new \App\Controller\Controller();

    $cont->test();
}

if ($_SERVER['REQUEST_URI'] === '/login') {
    $cont = new \App\Controller\AuthController();

    $cont->login();
}




