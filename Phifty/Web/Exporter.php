<?php

namespace Phifty\Web;

use Phifty\Singleton;
use Phifty\Action\ActionRunner;
use Phifty\AssetLoader;

class Exporter
{
    public $vars = array();

    public function __construct( ) 
    {
        $this->vars['Env'] = array( 
            'request' => $_REQUEST,
            'post'    => $_POST,
            'get'     => $_GET,
            'session' => $_SESSION,
            'cookie'  => $_COOKIE,
            'files'   => $_FILES,
            'server'  => $_SERVER,
            'env'     => $_ENV,
            'globals' => $GLOBALS,
        );

        /* register action result */
        $this->vars['Action']      = array( 'results' => ActionRunner::getInstance()->results );
        $this->vars['Kernel']      = kernel();
        $this->vars['CurrentUser'] = kernel()->currentUser;
        $this->vars['Web']         = new \Phifty\Web;
    }

    public function add( $name , $value ) 
    {
        $this->vars[ $name ] = $value;
    }

    public function __set( $name , $value ) 
    {
        $this->vars[ $name ] = $value;
    }

    public function __get( $name ) 
    {
        return @$this->vars[ $name ];
    }

    public function getVars()
    {
        return $this->vars;
    }

}

