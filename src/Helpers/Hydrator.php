<?php

namespace App\Helpers;

/**
 * Hydrates given class by calling getter / setter methods
 */
class Hydrator
{
    /**
     * Hydrates Class with given data
     *
     * @param  array  $data
     * @param  string $className
     * @return object
     */
    public function hydrate(array $data, string $className): object
    {
        $object = new $className();
        
        foreach ($data as $property => $value) {
            $setter = $this->getSetterMethodName($property);
            
            if (method_exists($object, $setter)) {
                $object->$setter($value);
            } else {
                $object->$property = $value;
            }
        }
        
        return $object;
    }
    
    /**
     * Returns Setter Proeprty name to call setter function.
     *
     * @param  string $property
     * @return string
     */
    private function getSetterMethodName(string $property): string
    {
        $parts = explode('_', $property);
        $parts = array_map('ucfirst', $parts);
        $methodName = 'set' . implode('', $parts);
        
        return $methodName;
    }
}