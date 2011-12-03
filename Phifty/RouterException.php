<?php

namespace Phifty;
use Exception;

class RouterException extends Exception
{
    public $router;

    function __construct( $router , $message ) 
    {
        $this->message = $message;
        $this->router = $router;
    }

    function getRouter()
    {
        return $this->router;
    }

}
