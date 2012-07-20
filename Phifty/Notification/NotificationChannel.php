<?php
namespace Phifty\Notification;
use ZMQ;
use ZMQSocket;
use ZMQContext;
use ZMQSocketException;

class NotificationChannel
{
    public $id;
    public $encoder;
    public $decoder;

    function __construct($channelId, $encoder = null, $decoder = null) {
        $this->id = $channelId;

        if( $encoder ) {
            $this->encoder = $encoder;
        }
        if( $decoder ) {
            $this->decoder = $decoder;
        }

        if( ! $encoder && ! $decoder ) {
            if( extension_loaded('mongo') ) {
                $this->encoder = 'bson_encode';
                $this->decoder = 'bson_decode';
            }
            elseif( extension_loaded('json') ) {
                $this->encoder = 'json_encode';
                $this->decoder = 'json_decode';
            }
        }
    }

    function publish($message) {
        $payload = is_string($message) 
                    ? $message 
                    : call_user_func($this->encoder,$message); 

        $bind = 'ipc://ntf-server';
        $context = new ZMQContext(1);
        $requester = new ZMQSocket($context, ZMQ::SOCKET_REQ);
        $requester->connect($bind);

        //  Socket to talk to server
        $requester->send( $this->id . ' ' . $payload);
        $string = $requester->recv();
        return $string === '1';
    }
}


