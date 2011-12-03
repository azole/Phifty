<?php
namespace Phifty;

use Exception;
use Phifty\RouterException;

const debug = 1;

/*
 * init a controller class ,or init a template engine
 * run the controller, or run the template engine rendering.
 *
 *
 * 
 * @param array $route route config
 *
 */

class RouterProxy2
{
    /* should use protected */
    public $rule;
	public $queryPath;

    function __construct( $queryPath , $rule )
    {
		$this->queryPath = $queryPath;
        $this->rule = $rule;
    }

    function getRule()
    {
        return $this->rule;
    }

	function runController( $route )
	{
		$class = $route['controller'];
		$obj = new $class( $this->rule );
		$obj->beforeRun();
		$content = $obj->run();  # controller found and run.
		$obj->afterRun();
		return $content;
	}

	function runRouterSet( $routeTo )
	{
        $class = $routeTo['routerset'];


        /* cut path after mount root */
        $basepath = substr( $this->queryPath , 0 , strlen($this->rule['path']) );
        $subpath = substr( $this->queryPath , strlen($this->rule['path']) );
        if( ! $subpath )
            $subpath = '/';
        
        $content = null;

        
        $routerset = new $class( $this->rule );
        $rule = $routerset->dispatch( $subpath );
        if( $rule ) {
            $routerset->beforeRun();
            $routerset->beforeRoute( $subpath,$routerset->env);
            $content = call_user_func( array($routerset,$rule['route']['method']) , $routerset->env );
            $routerset->afterRoute( $subpath,$routerset->env );
            $routerset->afterRun();
        }
        return $content;
	}

    function run( )
    {
        $routeTo = $this->rule['route'];

		if( is_array($routeTo) ) 
		{
            /* Use engine? 
             *
             * args =>
             *    engine => string {php,twig,smarty}
             *    template => string template_path
             *    args => array|function() {  }
             *
             * */
            $content = null;
			if( isset( $routeTo['controller'] ) ) 
			{
				$content = $this->runController( $routeTo );
            }
			elseif( isset( $routeTo['routerset'] ) ) 
			{
                $content = $this->runRouterSet( $routeTo );
			}
			elseif( isset( $routeTo['template'] ) ) 
			{
				$content = $this->runTemplate( $routeTo );
            }
			elseif( isset( $routeTo['callback'] ) ) 
			{
				if( is_callable($routeTo) ) {
					$content = $routeTo();
				} else {
					throw new \Exception( "Router {$routeTo['path']} is not callable." );
				}
			}
            else {
                throw new \Exception( "Unknown Router." );
            }
            print $content;
        }
        else {
            throw new \Exception( "Unknown Router or Empty Router" );
        }
    }


}


