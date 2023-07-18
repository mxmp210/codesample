<?php
namespace App\Helpers;

use Clue\React\Redis\RedisClient;

/**
 * Singleton Cache class to wrap connection initialization.
 * Call getInstance to get Cache instance.
 * @method RedisClient getInstance()
 */
class Cache {

    /** @var RedisClient */
    static private $instance;

    /**
     * Creates HTTP Server
     * @return RedisClient
    */
    private function createConnection() : RedisClient  {
        // Load Database Connection & Create Factory
        $protocol = DotEnv::env('REDIS_PROTOCOL', null);
        $host = DotEnv::env('REDIS_HOST', null);
        $port = DotEnv::env('REDIS_PORT', null);
        $database = DotEnv::env('REDIS_DATABASE', null);
        $password = DotEnv::env('REDIS_PASSWORD', null);
        $timeout = DotEnv::env('REDIS_TIMEOUT', 10);
        

        if(!$protocol || !$host || !$port || !$database ) {
            throw new \Exception("Please setup REDIS_PROTOCOL, REDIS_HOST, REDIS_PORT, REDIS_DATABASE in .env, ensure they are correct.");
        }

        // Check for existing conenction
        if(self::$instance != null) {
            return self::$instance;
        }

        // Connection String
        $connectionString = $protocol . '://';
        $connectionString .= $host;
        $connectionString .= ':' . $port;

        if(!empty($password) || !empty($database)) {
            $params = [
                'password' => $password,
                'db' => $database,
                'idle' => $timeout
            ];
            $connectionString .= '?' . http_build_query($params);
        }

        // No initial conenction is made - create new instance
        $redis = new RedisClient($connectionString);
        self::$instance = $redis;

        return self::$instance;
    }

    /**
     * Returns Singleton Instance of Cache Instance
     * @return RedisClient
     */
    public static function getInstance() : RedisClient
    {
        if(self::$instance == null) {
            $newInstance = new Cache();
            return $newInstance->createConnection();
        }
        return self::$instance;
    }
}

