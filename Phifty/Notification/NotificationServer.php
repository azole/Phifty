<?php
namespace Phifty\Notification;
use ZMQ;
use ZMQSocket;
use ZMQContext;
use ZMQSocketException;

class NotificationServer
{
    public $context;

    public $responder;

    public $publiser;

    public $decoder = 'json_encode';

    public $encoder = 'json_decode';

    function __construct($options = array())
    {
        if( isset($options['decoder']) ) {
            $this->decoder = $options['decoder'];
        }
        if( isset($options['encoder']) ) {
            $this->encoder = $options['encoder'];
        }
    }

    function connect($bind,$publishPoint) {
        $this->context = new ZMQContext(1);

        //  Socket to talk to clients
        $this->responder = new ZMQSocket($this->context, ZMQ::SOCKET_REP);
        $this->responder->bind($bind);

        $this->publisher = new ZMQSocket($this->context, ZMQ::SOCKET_PUB);
        $this->publisher->bind($publishPoint);
    }

    function start() {
        while(true) {
            //  Wait for next request from client
            $msg = $this->responder->recv();

            // printf("Received request: [%s]%s", $msg, PHP_EOL);

            $this->publisher->send($msg);

            //  Send reply back to client
            $this->responder->send('{"success":1}');
        }
    }
}





