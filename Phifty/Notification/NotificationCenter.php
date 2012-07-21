<?php
namespace Phifty\Notification;
use Exception;

class NotificationCenter
{
    public $encoder;
    public $decoder;

    public $publishPoint;
    public $subscribePoint;

    /**
     * Notification config stash
     */
    public $config;

    function __construct() {
        if( extension_loaded('mongo') ) {
            $this->encoder = 'bson_encode';
            $this->decoder = 'bson_decode';
        }
        elseif( extension_loaded('json') ) {
            $this->encoder = 'json_encode';
            $this->decoder = 'json_decode';
        }
        $this->config = kernel()->config->framework->Notification;
        $this->publishPoint = $this->config->PublishPoint ?: 'tcp://*:5555';
        $this->subscribePoint = $this->config->subscribePoint ?: 'tcp://*:5556';
    }

    function createFilter($id) {
        if( strlen($id) > 13 ) {
            throw new Exception('Filter string length exceed.');
        }
        return sprintf('% 13s',$id); // 13 chars for uniqid
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


