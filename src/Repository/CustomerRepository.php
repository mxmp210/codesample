<?php

namespace App\Repository;

use Cycle\ORM\Select;

class CustomerRepository extends \Cycle\ORM\Select\Repository
{
    /** Returns Collection with pager */
    public function listPage($limit, $offset): Select
    {
        return $this->select()->limit($limit)->offset($offset);
    }
}