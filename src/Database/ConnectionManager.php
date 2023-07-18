<?php
namespace App\Database;

use App\Helpers\DotEnv;
use Cycle\Database\DatabaseManager;
use Cycle\Database\Config;
use Cycle\Database\DatabaseInterface;

/**
 * Singleton Databse class to wrap databse initialization.
 * Call getInstance to get database connection.
 * @method DatabaseManager getInstance()
 */
class ConnectionManager {

    /** @var DatabaseManager  */
    static private $instance;

    /**
     * Creates Connection to Databse
     * @return DatabaseManager
     */
    private function createConnection() : DatabaseManager  {
        // Load Database Connection & Create Factory
        $host = DotEnv::env('DB_HOST', null);
        $port = DotEnv::env('DB_PORT', null);
        $database = DotEnv::env('DB_DATABASE', null);
        $user = DotEnv::env('DB_USER', null);
        $password = DotEnv::env('DB_PASSWORD', null);

        if(!$host || !$port || !$database || !$user) {
            throw new \Exception("Please setup DB_HOST, DB_PORT, DB_DATABASE, DB_USER, DB_PASSWORD in .env, ensure they are correct.");
        }

        // Check for existing conenction
        if(self::$instance != null) {
            return self::$instance;
        }

        // No initial conenction is made - create new instance

        $dbal = new DatabaseManager(
            new Config\DatabaseConfig([
                'default' => 'default',
                'databases' => [
                    'default' => ['connection' => 'mysql']
                ],
                'connections' => [
                    'mysql' => new Config\MySQLDriverConfig(
                        connection: new Config\MySQL\TcpConnectionConfig(
                            database: $database,
                            host: $host,
                            port: $port,
                            user: $user,
                            password: $password,
                        ),
                        queryCache: true
                    ),
                ]
            ])
        );

        self::$instance = $dbal;
        // print_r($dbal->database('default')->getTables());

        return self::$instance;
    }

    /**
     * Returns Singleton Instance of Databse Connection
     * @return ConnectionInterface
     */
    public static function getDatabaseInstance(string $database) : DatabaseInterface
    {
        if(self::$instance == null) {
            $newInstance = new ConnectionManager();
            return $newInstance->createConnection()->database($database);
        }
        return self::$instance->database($database);
    }



    /**
     * Returns Singleton Instance of Databse Connection
     * @return ConnectionInterface
     */
    public static function getInstance() : DatabaseManager
    {
        if(self::$instance == null) {
            $newInstance = new ConnectionManager();
            return $newInstance->createConnection();
        }
        return self::$instance;
    }
}

