<?php
namespace Phifty\Notification;
use ZMQ;
use ZMQSocket;
use ZMQContext;
use ZMQSocketException;
use Exception;

class NotificationQueue
{

    /**
     * @var string Subscriber Identity
     */
    public $id;

    /**
     * @var ZMQSocket
     */
    public $subscriber;

    function __construct($id = null, $center = null) {
        $this->id = $id ?: uniqid();
        $this->center = $center ?: NotificationCenter::getInstance();
        $this->subscriber = new ZMQSocket($this->center->context, ZMQ::SOCKET_SUB);
        $this->subscriber->setSockOpt( ZMQ::SOCKOPT_IDENTITY , $this->id );
        $this->subscriber->connect( $this->center->subscribePoint );
    }

    function unsubscribe($channel) {
        $id = is_string($channel) ? $channel : $channel->id;
        if( ! $id )
            throw new Exception('Undefined channel ID');
        $filter = $this->center->createFilter($id);
        $this->subscriber->setSockOpt(ZMQ::SOCKOPT_UNSUBSCRIBE, $filter);
    }

    function subscribe($channel) {
        $id = is_string($channel) ? $channel : $channel->id;
        if( ! $id )
            throw new Exception('Undefined channel ID');
        $filter = $this->center->createFilter($id);
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
