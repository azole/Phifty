<?php
namespace Phifty;
use Phifty\Action\ActionRunner;

/*
    MicroApp is the base class of App, Core, {Plugin} class.
*/

interface MicroAppInterface
{

    function page( $path, $template );

    function route( $path , $args );

    function js();

    function css();

}

class MicroApp extends \Phifty\Singleton
    implements MicroAppInterface
{
    public $basePath = '';

    function init()
    {

    }

    function getId()
    {
        return $this->baseClass();
    }


    /* get the base class (namespace) name,
     *
     * for \Product\Application, we get Product.
     *
     * */
    function baseClass()
    {
        if( class_exists('ReflectionClass') ) 
        {
            $object = new \ReflectionObject($this);
            $ns = $object->getNamespaceName();
            return $ns;
        } 
        else 
        {
            $class = get_class( $this );
            list( $ns, $rest ) = explode('\\',$class,2);
            return $ns;
        }
    }

    /* helper method */
    function page( $pattern , $template  )
    {
        $args = array();
        $args['template'] = $template;
        $args['args'] = array();
        webapp()->dispatcher->add( $pattern , $args );
    }

    /* 
     * locate plugin app dir path.
     * 
     * */
    function locate()
    {
        $object = new \ReflectionObject($this);
        return dirname($object->getFilename());
    }

    /* get the model in the namespace of current microapp */
    public function getModel( $name )
    {
        $object = new \ReflectionObject($this);
        $ns = $object->getNamespaceName();
        $modelClass = $ns . "\\Model\\" . $name;
        return new $modelClass;
    }

    /* mount a routerSet at somewhere */
    public function routeToSet( $pattern , $routerSetClass )
    {
        // this will expand router set to routers
        webapp()->dispatcher->add( $pattern, new $routerSetClass );
    }


    /*
     * in route method, we can do route with:
     *
     * $this->route('/path/to', array( 'controller' => 'ControllerClass' ))
     * $this->route('/path/to', 'ControllerClass' )
     * $this->route('/path/to', 'ControllerClass:actionName' )  # mapping to actionNameAction method.
     *
     * $this->route('/path/to', array( 'template' => 'template_file.html', 'args' => array( ... ) ) )
     *
     */
    public function route( $pattern, $args, $extra = null)
    {
        /* if args is string, it's a controller class */
        if( is_string($args)  ) 
        {
            $class = $args;

            /* If it's not full-qualified classname, we should prepend our base namespace. */
            if( strpos( $class , '\\' ) !== 0 ) 
                $class = '\\' .  $this->baseClass() . "\\Controller\\$class";

            /* extract action method name out, and set default to run method. */
            $action = 'index';
            if( ($pos = strrpos($class,':')) !== false ) {
                list($class,$action) = explode(':',$class);
            }
            $route = array(
                'controller' => $class,
                'action'     => $action,
            );
            webapp()->dispatcher->add( $pattern, $route , $extra );
        }
        elseif ( is_array($args) )      /* is a raw route data array */
        {
            webapp()->dispatcher->add( $pattern, $args , $extra );
        }
    }

    function js() { return array(); }
    function css() { return array(); }

    /* register CRUD actions */
    function withCRUDAction( $model , $types )
    {
        $runner = ActionRunner::getInstance();
        $runner->addCRUD( $this->baseClass() , $model , (array) $types );
    }
}
