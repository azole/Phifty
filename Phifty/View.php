<?php
namespace Phifty;
use Exception;
use ArrayAccess;
use IteratorAggregate;
use Universal\Http\HttpRequest;

class View
    implements ArrayAccess, IteratorAggregate
{
    public $args = array();

    protected $engine;

    protected $defaultEngine = 'smarty';

    function __construct( $engine = null , $engineOpts = null ) 
    {
        $this->setupEngine( $engine , $engineOpts );
        $this->init();

        // register args
        $this->args['Kernel']      = kernel();
        $this->args['Request'] = new HttpRequest;

        $this->args['Web']         = new \Phifty\Web;

        kernel()->event->trigger('view.init', $this);
    }

    function init()
    {

    }

    function setupEngine( $engine = null , $engineOpts = null )
    {
        if( $engine ) {
            /* if it's an engine object already, just save it */
            if( is_object( $engine ) )
                $this->engine = $engine;
            else
                $this->engine = \Phifty\View\Engine::createEngine( $engine , $engineOpts );
        } else {
            /* get default engine from config */
            $backend = kernel()->config->get('framework','View.Backend') ?: 'twig';
            $this->engine = \Phifty\View\Engine::createEngine( $backend , $engineOpts );
        }
    }

    function __set( $name , $value )
    {
        $this->args[ $name ] = $value;
    }

    function __get( $name )
    {
        return $this->args[ $name ];
    }


    /*
     * Assign template variable
     *
     * ->assign( array( .... ) );
     * ->assign( key , value );
     *
     */
    function assign()
    {
        $args = func_get_args();
		if( is_array( $args[0] ) ) {
            foreach( $args[0] as $k => $v ) {
                $this->args[ $k ] = $v;
            }
        }
        elseif( count($args) == 2 ) {
            list($name,$value) = $args;
            $this->args[ $name ] = $value;
        } else {
            throw new Exception( "Unknown assignment of " . __CLASS__ );
        }
    }

    /*
     * Get template arguments
     * 
     * @return array template arguments
     */
    function getArgs()
    {
        return $this->args;
    }


    /*
     * Setup template arguments
     *
     * @param array $args 
     */
    function setArgs($args)
    {
        $this->args = $args;
    }

    function getEngine()
    {
        return $this->engine;
    }


    /*
     * Default render method, can be overrided from View\Engine\Twig or View\Engine\Smarty
     *
     * Render template file.
     * @param string $template template name
     */
    function render($template)
    {
        return $this->engine->render( $template , $this->args );
    }


    /* 
     * Render template from string
     * @param string $stringTemplate template content
     * */
    function renderString( $stringTemplate )
    {
        return $this->engine->renderString( $stringTemplate , $this->args );
    }


    /*
     * Call render method to render
     */
    function __toString()
    {
        return $this->render();
    }

    public function offsetSet($name,$value)
    {
        $this->args[ $name ] = $value;
    }

    public function offsetExists($name)
    {
        return isset($this->args[ $name ]);
    }

    public function offsetGet($name)
    {
        return $this->args[ $name ];
    }

    public function offsetUnset($name)
    {
        unset($this->args[$name]);
    }

    public function getIterator() 
    {
        return new ArrayIterator( $this->args );
    }


}

