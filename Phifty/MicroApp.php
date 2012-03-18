<?php
namespace Phifty;
use Phifty\Action\ActionRunner;
use ReflectionClass;
use ReflectionObject;

/**
 *  MicroApp is the base class of App, Core, {Plugin} class.
 */
class MicroApp extends \Phifty\Singleton
{
    public $basePath = '';

    function init()
    {

    }

    function getId()
    {
        return $this->getNamespace();
    }


    /**
     * get the namespace name,
     *
     * for \Product\Application, we get Product.
     *
     * */
    public function getNamespace()
    {
        $object = new ReflectionObject($this);
        return $object->getNamespaceName();
    }

    /**
     * helper method, route path to template
     */
    function page( $path , $template  )
    {
        $this->add( $path , array( 
            'template' => $template,
            'args' => array() ,
        ));
    }

    /**
     * Locate plugin app dir path.
     */
    function locate()
    {
        $object = new ReflectionObject($this);
        return dirname($object->getFilename());
    }

    /**
     * get the model in the namespace of current microapp 
     */
    public function getModel( $name )
    {
        $class = sprintf('%s\Model\%s',$this->getNamespace(),$name);
        return new $class;
    }

    public function getController( $name )
    {
        $class = sprintf('%s\Controller\%s',$this->getNamespace(),$name);
        return new $class;
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
        $router = kernel()->router;

        /* if args is string, it's a controller class */
        if( is_array($args) ) 
        {
            // call template controller
            if( isset($args['template']) ) {
                $options[':args'] = array( 
                    'template' => $args['template'],
                    'args' => @$args['args'],
                );
                $router->add( $path , 'Phifty\Routing\TemplateController' , $options );
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
            else {
                $class = $args;
            }


            /* If it's not full-qualified classname, we should prepend our base namespace. */
            if( 0 !== strpos( $class , '\\' ) )  {
                $class = $this->baseClass() . "\\Controller\\$class";
            }

            if( ! method_exists($class,$action) ) {
                $action = 'run';
            }

            $args = $class . ':' . $action;
            $router->add( $path , $args , $options );
        }
        else {
            throw new Exception( "Unkown route argument." );
        }
    }

    public function expandRoute($path,$class)
    {
        $routes = $class::expand();
        kernel()->router->mount( $path , $routes );
    }

    public function js() { 
        return array(); 
    }

    public function css() { 
        return array(); 
    }

    /* register CRUD actions */
    public function withCRUDAction( $model , $types )
    {
        $runner = ActionRunner::getInstance();
        $runner->addCRUD( $this->baseClass() , $model , (array) $types );
    }
}
