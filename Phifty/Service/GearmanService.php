<?php
namespace Phifty\Service;
use GearmanClient;
use GearmanWorker;
use Phifty\Config\Accessor;
use Universal\Container\ObjectContainer;
use Exception;

class GearmanService 
    implements ServiceInterface
{
    public function getId() { return 'Gearman'; }

    public function register($kernel, $options = array() )
    {
        $options = new Accessor($options);
        $kernel->gearman = function() use ($options) {
            $container = new ObjectContainer;
            $container->client = function() use ($options) { 
                $client = new GearmanClient;
                if( $servers = $options->Servers ) {
                    if( is_string( $servers ) ) {
                        $client->addServers( $servers );
                    } elseif( is_array($servers) ) {
                        foreach( $servers as $server ) {
                            $parts = explode(':',$server);
                            $success = $client->addServer( $parts[0] , @$parts[1] );
                            if( false === $success ) 
                                throw new Exception("Gearman client connect failed.");
                        }
                    }
                }
                return $client;
            };

            $container->worker = function() use ($options) { 
                $worker = new GearmanWorker;
                if( $servers = $options->Servers ) {
                    if( is_string( $servers ) ) {
                        $worker->addServers( $servers );
                    } elseif( is_array($servers) ) {
                        foreach( $servers as $server ) {
                            $parts = explode(':',$server);
                            $success = $worker->addServer( $parts[0] , @$parts[1] );
                            if( false === $success ) 
                                throw new Exception("Gearman worker connect failed.");
                        }
                    }
                }
                return $worker;
            };
            return $container;
        };
    }
}

