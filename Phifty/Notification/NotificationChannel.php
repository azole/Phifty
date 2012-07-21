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
    public $center;
    public $filter;
    public $requester;

    function __construct($channelId = null, $encoder = null) 
    {
        $this->id = $channelId ?: uniqid();
        $this->center = NotificationCenter::getInstance();
        $this->encoder = $encoder ?: $this->center->getEncoder();
        $this->filter = $this->center->createFilter($this->id);

        $bind = 'tcp://localhost:5555';
        $context = new ZMQContext(1);
        $this->requester = new ZMQSocket($context, ZMQ::SOCKET_REQ);
        $this->requester->connect($bind);
    }

    function publish($message) {
        $payload = is_string($message) 
                    ? $message 
                    : call_user_func($this->encoder,$message); 

        //  Socket to talk to server (REP-REQ)
        $this->requester->send( $this->filter . ' ' . $payload);
        return $requester->recv() === '1';
    }
}


