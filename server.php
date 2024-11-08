<?php

namespace Chatapp;

require __DIR__.'/vendor/autoload.php';

use Dotenv\Dotenv;
use Chatapp\Socket\ChatServer;

(Dotenv::createImmutable(__DIR__))->load();

date_default_timezone_set('America/Mexico_City');

$ChatServer = new ChatServer(
    host: 'localhost',
    location: $_ENV['APP_LOCATION'],
    port: $_ENV['APP_PORT'],
);

//start endless loop, so that our script doesn't stop
while (true) {
	$ChatServer->process();
}

