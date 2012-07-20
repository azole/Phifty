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

    function __construct($channelId, $encoder = null) 
    {
        $this->id = $channelId;

        if( $encoder ) {
            $this->encoder = $encoder;
        }

        if( ! $encoder ) {
            if( extension_loaded('mongo') ) {
                $this->encoder = 'bson_encode';
            }
            elseif( extension_loaded('json') ) {
                $this->encoder = 'json_encode';
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

        $filter = sprintf('% 10s',$id);

        //  Socket to talk to server
        $requester->send( $filter . ' ' . $payload);
        return $requester->recv() === '1';
    }
}


