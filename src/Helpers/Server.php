<?php
namespace App\Helpers;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;

/**
 * Singleton Server class to wrap server initialization.
 * Call getInstance to get Server instance.
 * @method HttpServer getInstance()
 */
class Server {

    /** @var HttpServer */
    static private $instance;
    /**
     * Creates HTTP Server
     * @return HttpServer
     */
    private function createServer() : HttpServer {
        // Check for existing instance
        if(self::$instance != null) {
            return self::$instance;
        }

        // No initial server is initialised - create new instance
        $newServer = new HttpServer(function(ServerRequestInterface $request, callable $next) : \Psr\Http\Message\ResponseInterface {
            return new Response(
                Response::STATUS_IM_A_TEAPOT,
                ['Content-Type' => 'application/json' ],
                json_encode(
                    'No Server Implementation provided, Please Use Registry Routes to register controllers'
                ));
        });

        self::$instance = $newServer;
        return $newServer;
    }

    /**
     * Returns Singleton Instance of Server Instance
     * @return HttpServer
     */
    public static function getInstance() : HttpServer
    {
        if(self::$instance == null) {
            $newInstance = new Server();
            return $newInstance->createServer();
        }
        return self::$instance;
    }
}

