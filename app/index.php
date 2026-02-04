<?php

declare(strict_types=1);


require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/functions/functions.php';

if ($_SERVER['REQUEST_URI'] === '/favicon.ico') {
    return;
}

$cont = new \App\Controller\Controller();

$cont->test();



