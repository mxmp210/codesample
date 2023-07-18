<?php

use FastRoute\RouteCollector;

return function (RouteCollector $router) {
    // return function (\FrameworkX\App $app) {
    // Commong Routes
    $router->addRoute('GET', '/', [App\Controller\DefaultController::class, 'index']);
    $router->addRoute('GET', '/favicon.ico', [App\Controller\DefaultController::class, 'favicon']);

    // Resource : Customer
    $router->addGroup('/api', function (RouteCollector $api) {
        $api->addGroup('/v1', function (RouteCollector $v1) {
            $v1->addGroup('/customers', function (RouteCollector $cusomers) {
                $cusomers->get('/', [App\Controller\CustomerController::class, 'index']);
                $cusomers->get("/{id}", [App\Controller\CustomerController::class, 'show']);
                $cusomers->put('/', [App\Controller\CustomerController::class, 'update']);
                $cusomers->post('/', [App\Controller\CustomerController::class, 'create']);
                $cusomers->delete('/', [App\Controller\CustomerController::class, 'delete']);
            });
        });
    });
};
