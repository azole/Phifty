<?php
namespace Phifty\Notification;
use ZMQ;
use ZMQSocket;
use ZMQContext;
use ZMQSocketException;
use Exception;

class NotificationServer
{
    public $frontendPoint;

    public $backendPoint;

    public $context;

    public $responder;

    public $publiser;

    function __construct() { }

    function connect($bind,$publishEndPoint) {
        $this->frontendPoint = $bind;
        $this->backendPoint = $publishEndPoint;

        $this->context = new ZMQContext(1);

        //  Socket to talk to clients
        $this->responder = new ZMQSocket($this->context, ZMQ::SOCKET_REP);
        $this->responder->bind($this->frontendPoint);

        $this->publisher = new ZMQSocket($this->context, ZMQ::SOCKET_PUB);

        // Configure the maximium queue (buffer limit)
        $this->publisher->setSockOpt(ZMQ::SOCKOPT_HWM, 100);
        $this->publisher->bind($this->backendPoint);
    }

    function start() {
        while(true) {
            try {
                //  Wait for next request from client
                $msg = $this->responder->recv();

                // printf("Received request: [%s]%s", $msg, PHP_EOL);
                $this->publisher->send($msg);

            } catch ( Exception $e ) {
                $this->responder->send('0');
                echo $e;
            }
            //  Send reply back to client
            $this->responder->send('1');
        }
    }
}





