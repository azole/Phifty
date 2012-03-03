<?php
namespace Phifty;
use Phifty\Action\ActionRunner;
use ReflectionClass;
use ReflectionObject;

/*
    MicroApp is the base class of App, Core, {Plugin} class.
*/

class MicroApp extends \Phifty\Singleton
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
            $object = new ReflectionObject($this);
            return $object->getNamespaceName();
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
        $object = new ReflectionObject($this);
        return dirname($object->getFilename());
    }

    /* get the model in the namespace of current microapp */
    public function getModel( $name )
    {
        $object = new ReflectionObject($this);
        $ns = $object->getNamespaceName();
        $modelClass = $ns . "\\Model\\" . $name;
        return new $modelClass;
    }


    /**
     * in route method, we can do route with:
     *
     * $this->route('/path/to', array( 
     *          'controller' => 'ControllerClass'
     *  ))
     * $this->route('/path/to', 'ControllerClass' );
     *
     * Mapping to actionNameAction method.
     *
     * $this->route('/path/to', 'ControllerClass:actionName' )  
     *
     * $this->route('/path/to', array( 
     *          'template' => 'template_file.html', 
     *          'args' => array( ... ) )
     * )
     */
    public function route( $path, $args, $options = array() )
    {
        $router = webapp()->router;

        /* if args is string, it's a controller class */
        if( is_array($args) ) 
        {
            // call template controller
            if( isset($args['template']) ) {
                $options[':args'] = array( 
                    'template' => $args['template'],
                    'args' => @$args['args'],
                );
                $router->add( $path , 'Phifty\Routing\Route\TemplateRoute' , $options );
            }
            elseif( isset($args['controller']) ) {
                $router->add( $path , $args['controller'], $options );
            }
        }
        elseif( is_string($args)  ) 
        {
            /* extract action method name out, and set default to run method. */
            $class = null;
            $action = 'indexAction';
            if( false !== ($pos = strrpos($args,':')) ) {
                list($class,$action) = explode(':',$args);
                if( false === strpos( $action , 'Action' ) )
                    $action .= 'Action';
            }

            /* If it's not full-qualified classname, we should prepend our base namespace. */
            if( strpos( $class , '\\' ) !== 0 ) 
                $class = '\\' .  $this->baseClass() . "\\Controller\\$class";

            $args = $class . ':' . $action;
            $router->add( $path , $args , $options );
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
