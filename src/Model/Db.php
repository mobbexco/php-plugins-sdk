<?php

namespace Mobbex\Model;

class Db
{
    /**
     * Constructor
     * 
     * @param string $prefix
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Executes a query & return the results of the query or bool.
     * 
     * @param string $query
     * 
     * @return bool|array
     */
    public function query($query)
    {
        return false;
    }
}