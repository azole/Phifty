<?php
namespace Phifty\Routing;
use Exception;
use Phifty\Routing\RouteCompiler;
use Phifty\Routing\ApcRouteCompiler;
use Phifty\Routing\Route;

/* RouteDispatcher
 *
 */
class RouteDispatcher
{
	/* routes:
	 *
	 * array( pattern => route data, .... );
	 *
	 */
	public $routes = array();

    /*
     * Add route to the routing table,
     * mainly we take array (route array), string (route class name), object (route object)
     *
     */
	function add($pattern,$route)
	{
        if( strlen($pattern) > 1 )
            $pattern  = rtrim( $pattern , '/' );

        $compiler = webapp()->isDev ? new RouteCompiler : new ApcRouteCompiler;

        // expand routerset to routes
        if( is_object($route) && is_a( $route, '\Phifty\Routing\RouteSet' ) ) 
        {
            $routes = $route->getRoutes();
            foreach( $routes as $r ) {
                $r['prefix']   = $pattern;
                $expandPattern = $pattern . '/' . trim($r['pattern'],'/');
                $this->add( $expandPattern, $r );
            }
            return;
        }
        elseif( is_array( $route ) ) 
        {
            // compile pattern here, we will get an regular expression
            $route['pattern'] = $pattern;
            $compiledRoute = $compiler->compile( $route );

            // push the pattern to routing table. (map)
            $this->routes[] = $compiledRoute;
        }
        elseif( is_string($route) ) 
        {
            $class = $route;
            $route = array( 'pattern' => $pattern , 'controller' => $route , 'action' => 'indexAction' );
            $compiledRoute = $compiler->compile( $route );
            $this->routes[] = $compiledRoute;
        }
        else {
            throw new Exception('Unknown Route Type');
        }
	}

    function getRoutes()
    {
        return $this->routes;
    }

	function dispatch( $path ) 
	{
        foreach( $this->routes as $route ) {
            if( preg_match( $route['compiled'], $path, $regs ) ) {
                foreach( $route['variables'] as $k ) {
                    $route['vars'][ $k ] = $regs[$k];
                }
                return $route;
            }
        }
	}

    function run()
    {
        $path = '/';
        if( isset( $_SERVER['PATH_INFO'] ) )
            $path = $_SERVER['PATH_INFO'];
        $route = $this->dispatch( $path );

        # handle this for 404 not found.
        if( ! $route ) {
            $route = $this->dispatch('/not_found');
        }
        if( ! $route ) {
            throw new Exception('Route not found.');
        }

        // wrap route object and eval it
        $routeObj = Route::wrap($route);
        $content = $routeObj->evaluate();
        echo $content;
    }
}


