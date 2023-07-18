<?php

namespace App\Validator\Constraints;;

use Symfony\Component\Validator\Constraint;

/**
 * Defines unique customer attribute
 */
#[\Attribute]
class UniqueCustomerEmail extends Constraint {
    
    public $message = 'Duplicate customer : {{ email }}';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy()
    {
        return get_class($this).'Validator';
    }
} 