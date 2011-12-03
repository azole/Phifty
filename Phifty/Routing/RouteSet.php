<?php
namespace Phifty\Routing;
use Phifty\Controller;

abstract class RouteSet extends Controller
{
    public $routes = array();

    function __construct( $route = null )
    {
        parent::__construct( $route );
        $this->table();
    }

    function before() {  }

    function after()  {  }

    function route($pattern,$method,$opts = null )
    {
        $r = array(
            'pattern'    => $pattern,
            'controller' => get_class($this),
            'method'     => $method,
        );
        if( $opts ) {
            foreach( explode(' ','requirement default') as $key ) {
                if( isset($opts[$key] ) )
                    $r[$key] = $opts[$key];
            }
        }
        $this->routes[] = $r;
    }

    /* 
     *  Routing table
     *
     *   @return a router hash
     */
    function table() 
    {
        // $this->route( path , method );
    }

    function getRoutes()
    {
        return $this->routes;
    }

}

