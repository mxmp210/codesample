<?php
declare(strict_types=1);

use App\Helpers\DotEnv;
use App\Helpers\RouteRegistry;
use App\Helpers\Server;

// Load Dependencies
require __DIR__ . '/vendor/autoload.php';
// require __DIR__ . '/multiprocess.php';

// TODOv2 : Fork main process to scale into workers and get rid of framework-x
// $workerNumber = get_cpu_count();
// echo 'Starting ' . $workerNumber . ' servers on machine...\r\n';

// Load Env
DotEnv::loadEnv();

// Init new Application
$server = Server::getInstance();

// Route Config
$router = RouteRegistry::getInstance($server);
$router->register(require __DIR__ . '/src/Routes.php');

// Preload Connection Pools & Services at app startup to avoid cold starts
require __DIR__ . '/src/Preload.php';

// Run App
$socket = new React\Socket\SocketServer(DotEnv::env('APP_HOST', '127.0.0.1') . ":" . DotEnv::env('APP_PORT', '8080'));
$server->listen($socket);

echo 'Server running at ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
