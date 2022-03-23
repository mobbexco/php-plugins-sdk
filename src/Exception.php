<?php

namespace Mobbex;

defined('ABSPATH') || exit;

class Exception extends \Exception
{
    public $data = '';

    /**
     * Throw a exception with additional data.
     * 
     * @param string $message 
     * @param string $code
     * @param mixed $data
     */
    public function __construct($message = '', $code = 0, $data = '')
    {
        $this->data = $data;
        parent::__construct($message, $code);
    }
}