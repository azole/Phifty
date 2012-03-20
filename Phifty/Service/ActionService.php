<?php
namespace Phifty\Service;
use Exception;
use Phifty\Action\ActionRunner;

class ActionService
    implements ServiceInterface
{
    public function getId() { 
        return 'Action'; 
    }

    public function register($kernel, $options = array() )
    {
        $kernel->action = function() use ($options) {
            return new \Phifty\Action\ActionRunner;
        };

        $kernel->event->register('phifty.run',function() use ($kernel) {

            // check if there is $_POST['action'] or $_GET['action']
            if( isset($_REQUEST['action']) ) {
                try 
                {
                    $runner = $kernel->action;
                    $result = $runner->run();
                    if( $result && $runner->isAjax() ) {
                        echo $result;
                        exit(0);
                    }
                } catch( Exception $e ) {
                    /**
                    * return 403 status forbidden
                    */
                    header('HTTP/1.0 403');
                    if( $runner->isAjax() ) {
                        die( json_encode( array( 'error' => $e->getMessage() ) ) );
                    } else {
                        die( $e->getMessage() );
                    }
                }
            }
        });
    }
}








