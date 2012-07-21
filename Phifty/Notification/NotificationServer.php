<?php
namespace Phifty\Notification;
use ZMQ;
use ZMQSocket;
use ZMQContext;
use ZMQSocketException;
use Exception;

class NotificationServer
{
    public $center;

    public $responder;

    public $publiser;

    function __construct($center = null) {
        $this->center = $center ?: NotificationCenter::getInstance();
        $this->connectDevice( 
            $this->center->getPublishPoint(true), 
            $this->center->getSubscribePoint(true)
        );
    }

    function connectDevice($bind,$publishEndPoint) {
        //  Socket to talk to clients
        // $this->responder = new ZMQSocket($this->center->context, ZMQ::SOCKET_REP);
        $this->responder = new ZMQSocket($this->center->context, ZMQ::SOCKET_PULL);
        $this->responder->bind($bind);

        $this->publisher = new ZMQSocket($this->center->context, ZMQ::SOCKET_PUB);

        // High Water Mark
        // Configure the maximium queue (buffer limit)
        $this->publisher->setSockOpt(ZMQ::SOCKOPT_HWM, 100);
        $this->publisher->bind($publishEndPoint);
    }

    function start() {
        while(true) {
            try {
                //  Wait for next request from client
                $msg = $this->responder->recv();

                printf("Received request: [%s]%s", $msg, PHP_EOL);
                $this->publisher->send($msg);

            } catch ( Exception $e ) {
                echo $e;
            }
        }
    }
}





