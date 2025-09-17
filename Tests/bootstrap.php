<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

if (!isset($_SERVER['DATABASE_URL']) && !isset($_ENV['DATABASE_URL'])) {
    $dbPath = dirname(__DIR__).'/var/test.db';
    $_SERVER['DATABASE_URL'] = 'sqlite:///'.$dbPath;
    $_ENV['DATABASE_URL'] = $_SERVER['DATABASE_URL'];
}


if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
