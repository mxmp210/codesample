<?php

namespace App\Helpers;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Helps CLeaning up Pagination Parameters
 */
class PaginationHelper
{

    // Default values
    private $limit = 10;
    private $offset = 0;
    /**
     * Hydrates Class with given data
     *
     * @param  array  $data
     * @param  string $className
     * @return object
     */
    public function parseRequest(ServerRequestInterface $request): PaginationHelper
    {
        $params = $request->getQueryParams();

        if(array_key_exists('offset', $params)) {
            $this->offset = intval($params['offset'], 0);
        }

        if(array_key_exists('limit', $params)) {
            $this->limit = intval($params['limit'], 10);
        }

        if($this->offset <= 0) {
            $this->offset = 0;
        }

        if($this->limit < 1) {
            $this->limit = 1;
        }
        
        return $this;
    }
    
    /**
     * Returns parsed offset
     * @return int
     */
    public function getOffset(): int
    {       
        return $this->offset;
    }

    /**
     * Returns parsed offset
     * @return int
     */
    public function getLimit(): int
    {       
        return $this->limit;
    }
}
