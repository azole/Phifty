<?php
namespace Phifty\Notification;
use ZMQ;
use ZMQSocket;
use ZMQContext;
use ZMQSocketException;

class NotificationQueue
{
    public $context;
    public $subscriber;

    function __construct() {
        $this->context = new ZMQContext();
        $this->subscriber = new ZMQSocket($context, ZMQ::SOCKET_SUB);
        $this->subscriber->connect("tcp://localhost:5556");
    }

    function subscribe($channel) {
        $id = is_string($channel) ? $channel : $channel->id;

        //  Subscribe to zipcode, default is NYC, 10001
        $this->subscriber->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, $id );
    }

    function listen($callback) { 
        while(true) {
            $string = $subscriber->recv();
            list($id,$payload) = explode(' ',$string,2);
            echo $payload, "\n";
        }
    }
}

// new ZMQ(new ZMQContext(), ZMQ::SOCKET_REQ, "MySock1");
