<?php
namespace Phifty\Notification;
use ZMQ;
use ZMQSocket;
use ZMQSocketException;

class NotificationChannel
{
    public $id;
    public $encoder;
    public $center;
    public $filter;
    public $requester;

    function __construct($channelId = null, $center = null) 
    {
        $this->id = $channelId ?: uniqid();
        $this->center = $center ?: NotificationCenter::getInstance();
        $this->encoder = $this->center->getEncoder();
        $this->filter = $this->center->createFilter($this->id);
        // $this->requester = new ZMQSocket($this->center->context, ZMQ::SOCKET_REQ);
        $this->requester = new ZMQSocket($this->center->getContext(), ZMQ::SOCKET_PUSH);
        $this->requester->connect( $this->center->getPublishPoint() );
    }

    function publish($message) {
        $payload = $this->center->encode($message);

        //  Socket to talk to server (REP-REQ)
        return $this->requester->send( $this->filter . $payload);

        // For REQ MODE
        // return $this->requester->recv() === '1';
    }
}


