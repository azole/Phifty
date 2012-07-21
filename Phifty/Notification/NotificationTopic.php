<?php
namespace Phifty\Notification;
use ZMQ;
use ZMQSocket;
use ZMQSocketException;

class NotificationTopic
{
    public $id;
    public $encoder;
    public $center;
    public $requester;

    function __construct($topicId = null, $center = null) 
    {
        $this->id = $topicId ?: uniqid();
        $this->center = $center ?: NotificationCenter::getInstance();
        $this->encoder = $this->center->getEncoder();
        $this->requester = $this->center->createRequester();
    }

    function register() {
        $this->requester->send('reg ' . $this->id);
        return $this->requester->recv();
    }

    function unregister() {
        $this->requester->send('unreg ' . $this->id);
        return $this->requester->recv();
    }


    /**
     * Publish normal message
     *
     * @param mixed $message
     */
    function publish($message) {
        $payload = $this->center->encode($message);

        //  Socket to talk to server (REP-REQ)
        $this->requester->send( $this->id . ' ' . $payload);
        return $this->requester->recv() === '1';
    }
}


