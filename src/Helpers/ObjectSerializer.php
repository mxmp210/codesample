<?php
namespace App\Helpers;

use App\Normalizer\UuidNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Singleton Cache class to wrap connection initialization.
 * Call getInstance to get Cache instance.
 * @method RedisClient getInstance()
 */
class ObjectSerializer {

    /** @var Serializer */
    static private $instance;

    /**
     * Creates Object Serializer
     * @return Serializer
    */
    private function createSerializer() : Serializer  {
  
        // Check for existing instance
        if(self::$instance != null) {
            return self::$instance;
        }

        // Encoders
        $encoders = [new JsonEncoder(), new XmlEncoder()];
        $normalizers = [new UuidNormalizer(), new ObjectNormalizer()];

        $serializer = new Serializer($normalizers, $encoders);

        // No initial instance is made - create new instance
        self::$instance = $serializer;

        return self::$instance;
    }

    /**
     * Returns Singleton Instance of Serializer Instance
     * @return Serializer
     */
    public static function getInstance() : Serializer
    {
        if(self::$instance == null) {
            $newInstance = new ObjectSerializer();
            return $newInstance->createSerializer();
        }
        return self::$instance;
    }
}

