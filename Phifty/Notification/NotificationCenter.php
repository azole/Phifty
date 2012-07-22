<?php
namespace Phifty\Notification;
use Exception;
use ZMQ;
use ZMQSocket;
use ZMQContext;
use ZMQSocketException;

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

        $this->publishPoint = $this->config && $this->config->PublishPoint 
                                ? $this->config->PublishPoint  
                                : 'tcp://localhost:55555';
        $this->subscribePoint = $this->config && $this->config->subscribePoint 
                                ? $this->config->subscribePoint
                                : 'tcp://localhost:55556';
        $this->context = new ZMQContext(1);
    }

    function createRequester() {
        // $this->requester = new ZMQSocket($this->center->getContext(), ZMQ::SOCKET_PUSH);
        $requester = new ZMQSocket($this->getContext(), ZMQ::SOCKET_REQ);
        $requester->connect( $this->getPublishPoint() );
        return $requester;
    }


    function parseFilter($string) {
        return substr($string,0,13);
    }

    function splitMessage($string) {
        $filter = substr($string,0,13);
        $binary = substr($string,13);
        return array($filter,$binary);
    }


    function encode($payload) {
        return call_user_func($this->encoder,$payload);
    }

    function decode($payload) {
        return call_user_func($this->decoder,$payload);
    }

    function getSubscribePoint($forListen = false) {
        if( $forListen ) {
            preg_match('#^(\w+)://(.*?):(\d+)$#',$this->subscribePoint,$regs);
            return "{$regs[1]}://*:{$regs[3]}";
        }
        else {
            return $this->subscribePoint;
        }
    }

    function getContext() {
        return $this->context;
    }

    function getPublishPoint($forListen = false) { 
        if( $forListen ) {
            preg_match('#^(\w+)://(.*?):(\d+)$#',$this->publishPoint,$regs);
            return "{$regs[1]}://*:{$regs[3]}";
        }
        else {
            return $this->publishPoint;
        }
    }

    /**
     * Create a subscriber Id
     */
    function getSubscriberId($id = null) {
        $id = $id ?: uniqid();
        return $id;
    }

    function createFilter($id) {
        if( strlen($id) > 13 ) {
            throw new Exception('Filter string length exceed.');
        }
        return sprintf('%_13s',$id); // 13 chars for uniqid
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

    static function getInstance() { 
        static $ins;
        if( $ins )
            return $ins;
        return $ins = new static;
    }


}


