<?php

namespace Chatapp;

require __DIR__.'/config/app.php';

use Chatapp\Socket\ChatServer;

$ChatServer = new ChatServer(
    host: 'localhost',
    location: $_ENV['APP_LOCATION'],
    port: $_ENV['APP_PORT'],
);

//start endless loop, so that our script doesn't stop
while (true) {
	$ChatServer->process();
}

