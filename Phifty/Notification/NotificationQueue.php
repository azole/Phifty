<?php
namespace Phifty\Notification;
use ZMQ;
use ZMQSocket;
use ZMQContext;
use ZMQSocketException;
use Exception;

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

        if( ! $id )
            throw new Exception('Undefined channel ID');

        $filter = sprintf('% 10s',$id);

        //  Subscribe to zipcode, default is NYC, 10001
        $this->subscriber->setSockOpt(ZMQ::SOCKOPT_SUBSCRIBE, $filter);
    }

    function listen($callback) { 
        while(true) {
            $string = $subscriber->recv();
            list($id,$payload) = explode(' ',$string,2);
            call_user_func($callback,$id,$payload);
        }
    }
}

// new ZMQ(new ZMQContext(), ZMQ::SOCKET_REQ, "MySock1");
