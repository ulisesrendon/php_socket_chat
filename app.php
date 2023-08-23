<?php

namespace Chatapp;

require __DIR__.'/vendor/autoload.php';

use Dotenv\Dotenv;
use Chatapp\Shared\LogHelper;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model as Eloquent;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

(Dotenv::createImmutable(__DIR__))->load();

$capsule = new Capsule;
$capsule->addConnection([
    "driver" => $_ENV['DB_CONNECTION'],
    "host" => $_ENV['DB_HOST'],
    "port" => $_ENV['DB_PORT'],
    "database" => $_ENV['DB_DATABASE'],
    "username" => $_ENV['DB_USERNAME'],
    "password" => $_ENV['DB_PASSWORD'],
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

class Collection extends Eloquent{}

dump( Collection::all() );
