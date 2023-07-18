<?php
namespace App\Helpers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Promise\Promise;

/**
 * Singleton DotEnv class for .env initialization.
 * Call register to get DotEnv connection.
 * @method void register()
 */
class RouteRegistry {

    /** @var RouteRegistry */
    static private $instance;
    static private $server;

    /** Takes in App to register routes
     * @param HttpServer $server 
     */
    public function __construct(HttpServer $server) {
        self::$server = $server;
    }

    /**
     * Takes in AbstractController to registerin app registry
     * @param  string $name
     * @return void
     */
    public function register(callable $function)
    {
        $router = \FastRoute\simpleDispatcher($function);

        // Replace Empty Server with router implementation
        self::$server->__construct(
            new \React\Http\Middleware\StreamingRequestMiddleware(),
            new \React\Http\Middleware\LimitConcurrentRequestsMiddleware(10000), // 100 concurrent buffering handlers
            new \React\Http\Middleware\RequestBodyBufferMiddleware(2 * 1024 * 1024), // 8 MiB per request
            new \React\Http\Middleware\RequestBodyParserMiddleware(),
            function (ServerRequestInterface $request, callable $next) {
                $promise = \React\Promise\resolve($next($request));
                return $promise->then(function (ResponseInterface $response) use ($request) {
                    // Normal Response
                    return $response->withHeader('Server',  'Codesample/0.1');
                });
            },
            function (ServerRequestInterface $request, callable $next) {
                $promise = new \React\Promise\Promise(function ($resolve) use ($next, $request) {
                    $resolve($next($request));
                });
                return $promise->then(null, function (\Exception $e) {
                    self::$server->emit('error', array($e));
                    return $this->errorResponse($e);
                });
            },
            function (ServerRequestInterface $request) use ($router) : Response | Promise {
                $method = $request->getMethod();
                $uri = $request->getUri()->getPath();
            
                $routeInfo = $router->dispatch($method, $uri);
            
                switch ($routeInfo[0]) {
                    case \FastRoute\Dispatcher::NOT_FOUND:
                        return new Response(
                            Response::STATUS_NOT_FOUND,
                            [
                                'Content-Type' => 'application/json'
                            ],
                            json_encode([
                                'error' => '404 Not Found',
                                'message' => 'Requested resource is Not Found',
                            ])
                        );
                        break;
                    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                        $allowedMethods = $routeInfo[1];
                        return new Response(
                            Response::STATUS_METHOD_NOT_ALLOWED,
                            [
                                'Content-Type' => 'application/json'
                            ],
                            json_encode([
                                'error' => '405 Method Not Allowed',
                                'message' => 'Requested method is Not Allowed',
                            ])
                        );
                        break;
                    case \FastRoute\Dispatcher::FOUND:
                        $handler = $routeInfo[1];
                        $vars = $routeInfo[2];
                        list($controller, $method) = $handler;
                        $controllerInstance = new $controller();
                        $response = $controllerInstance->$method($request, $vars);
                        return $response;
                        break;
                }
        });

        self::$server->on('error', function(\Exception $error) {
            echo "Error : " . $error->getMessage() . PHP_EOL;
            echo $error->getTraceAsString() . PHP_EOL;
        });
    }

    private function errorResponse(\Exception $error) : Response {
        $payload = [
            'error' => '500 Internal Server Error',
            'message' => 'An error occoured while serving request',
        ];

        if(DotEnv::env('APP_ENV', 'prod') == 'dev') {
            $payload['errors'] = $error->__toString();
        }

        return new Response(
            Response::STATUS_INTERNAL_SERVER_ERROR,
            [
                'Content-Type' => 'application/json'
            ],
            json_encode($payload)
        );
    }

    /**
     * Returns RouteRegistry - Passing new server instance requires reregistration of routes
     * @return RouteRegistry
     */
    public static function getInstance(HttpServer $server) : RouteRegistry
    {
        if(self::$instance == null) {
            $newInstance = new RouteRegistry($server);
            return $newInstance;
        }

        // Link new Server Instance
        self::$server =  $server;
        return self::$instance;
    }
}
