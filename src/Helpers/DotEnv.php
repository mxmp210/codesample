<?php
namespace App\Helpers;

use Symfony\Component\Dotenv\Dotenv as EnvironmentLoader;

/**
 * Singleton DotEnv class for .env initialization.
 * Call loadEnv to get DotEnv connection.
 * @method void loadEnv()
 */
class DotEnv {

    /** @var DotEnv */
    static private $instance;

    /**
     * Loads .env from Project Root
     * @return void
     */
    private function load() : void {
        // Setup and load Environement variables
        $dotenv = new EnvironmentLoader();
        $dotenv->loadEnv(dirname(__DIR__) . '/../.env');
        self::$instance = $this;
    }

    /**
     * Returns environment variable - if not set returns default value.
     *
     * @param  string $name
     * @param  [type] $default
     * @return mixed
     */
    public static function env(string $name, $default = null)
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? $default;
        
        if ($value !== null && is_string($value) && strpos($value, '%') === 0) {
            $value = substr($value, 1, -1);
        }
        
        return $value;
    }

    /**
     * Returns environment variable
     *
     * @param  string $name
     * @return mixed
     */
    public static function getenv(string $value)
    {
        return self::env($value, null);
    }

    /**
     * Loads Environment
     * @return void
     */
    public static function loadEnv() : void
    {
        if(self::$instance == null) {
            $newInstance = new DotEnv();
            $newInstance->load();
        }
    }
}
