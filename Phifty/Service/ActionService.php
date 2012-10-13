<?php
namespace Phifty\Service;
use Exception;
use ActionKit\ActionRunner;

class ActionService
    implements ServiceInterface
{
    public function getId() { return 'action'; }

    public function register($kernel, $options = array() )
    {
        $action = ActionRunner::getInstance();
        $action->registerAutoloader();

        $kernel->action = function() use ($options,$action) {
            return $action;
        };

        $kernel->event->register('view.init', function($view) {
            $view->args['Action'] = ActionRunner::getInstance();
        });

        if( $kernel->hasBuilder('classloader') ) {
            $kernel->classloader->addNamespace(array( 
                'ActionKit' => array( 
                    $kernel->frameworkDir . DIRECTORY_SEPARATOR . 'src' 
                )
            ));
        }

        $kernel->event->register('phifty.before_path_dispatch',function() use ($kernel) {

            // check if there is $_POST['action'] or $_GET['action']
            if( isset($_REQUEST['action']) ) {
                try
                {
                    $runner = $kernel->action; // get runner
                    $result = $runner->run( $_REQUEST['action'] );
                    if( $result && $runner->isAjax() ) {
                        // it's JSON
                        header('Content-Type: application/json; Charset=utf-8');
                        echo $result->__toString();
                        exit(0);
                    }
                } catch( Exception $e ) {
                    /**
                     * Return 403 status forbidden
                     */
                    header('HTTP/1.0 403');
                    if( $runner->isAjax() ) {
                        die(json_encode(
                            array(
                                'error' => 1, 
                                'message' => $e->getMessage() 
                            )));
                    } else {
                        die( $e->getMessage() );
                    }
                }
            }
        });
    }
}
