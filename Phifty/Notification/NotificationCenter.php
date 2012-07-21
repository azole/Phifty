<?php
namespace Phifty\Notification;

class NotificationCenter
{
    public $encoder;

    public $decoder;

    function __construct() {
        if( extension_loaded('mongo') ) {
            $this->encoder = 'bson_encode';
            $this->decoder = 'bson_decode';
        }
        elseif( extension_loaded('json') ) {
            $this->encoder = 'json_encode';
            $this->decoder = 'json_decode';
        }
    }

    function createFilter($id) {
        return sprintf('% 10s',$id);
    }

    function getEncoder() { 
        return $this->encoder;
    }

    function getDecoder() {
        return $this->decoder;
    }

    function setEncoder() {
        return $this->encoder;
    }

    function getInstance() { 
        static $ins;
        if( $ins )
            return $ins;
        return $ins = new static;
    }


}


