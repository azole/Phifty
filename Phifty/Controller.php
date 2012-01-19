<?php
namespace Phifty;
use Phifty\Web\Request;
use Universal\Http\HttpRequest;
use ReflectionObject;
use YAMLKit\YAML;
use Exception;

interface ControllerInterface 
{
    public function after();
    public function before();

    function post( $env );
    function get( $env );
}

/*
    Synopsis

    $controller = new $class( $this );
    $controller->runAction( 'indexAction' , array(
        'vars' => array( name => $value ),
        'default' => array( ... ),
    ) );
*/

class Controller 
    implements ControllerInterface
{

    /* env request object, handles post, get, request objects */
    public $env;

    public $request;

    function __construct()
    {
        $this->init();
        // old: XXX
        $this->env   = new Request;
        $this->request = new HttpRequest;
    }

    public function init()
    {

    }

    public function getCurrentUser()
    {
        return webapp()->currentUser;
    }

    /* 
     * currentUserCan method
     *
     * provide a permission check.
     *
     */
    public function currentUserCan($user)
    {
        return true;
    }

    public function view( $options = null )
    {
        static $view;
        if( $view ) {
            if( $options )
                throw new Exception("The View object has been initialized.");
            return $view;
        }

        if( ! $options )
            $options = array();

        $templateEngine = webapp()->config('view.backend');
        $viewClass      = webapp()->config('view.class');
        if( ! $viewClass )
            throw new Exception('view.class config is not defined.');

        $engine         = \Phifty\View\Engine::createEngine( $templateEngine , $options );
        return $view = new $viewClass( $engine );  // pass 'Smarty' or 'Twig'
    }

    /*
    function run()
    {
        if( @$_POST )
            return $this->post( $this->env );
        else
            return $this->get( $this->env );
    }
    */

    /* web utils functions */
    function redirect($url)
    {
        header( 'Location: ' . $url );
    }

    function redirectLater($url,$seconds = 1 )
    {
        header( "refresh: $seconds; url=" . $url );
    }


    /* handle post data */
    function post($env)
    {

    }

    /* handle get data */
    function get($env)
    {


    }

    /* Move this into Agent class */
    function isMobile()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        return preg_match( '/(ipad|iphone|android)/i' ,$agent );
    }

    /*
     * Tell browser dont cache page content
     */
    function headerNoCache() 
    {
        header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
    }

    /*
     * Set cache expire time
     */
    function headerCacheTime( $time = null )
    {
        $datestr = gmdate(DATE_RFC822, $time );
        // header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
        header( "Expires: $datestr" );
    }



    /*
     * Render json content 
     *
     * @param array $data
     *
     */
    public function renderJson($data) 
    {
        /* XXX: dirty hack this for phpunit testing */
        if( ! CLI_MODE )
            header('Content-type: application/json; charset=UTF-8');
        return json_encode($data);
    }

    /* 
     * Render yaml
     *
     **/
    public function renderYaml($data)
    {
        if( ! CLI_MODE )
            header('Content-type: application/yaml; charset=UTF-8;');
        return YAML::dump( $data );
    }


    /**
     * Render page content
     *
     *     $this->renderPage( 'ViewPageClass' , array(  
     *          'i18n' => 1, 
     *          'layout' => 'layout.html', 
     *          'content' => 'content.html' ) );
     *
     */
    public function renderPage( $viewClass , $options = array() , $args = array() )
    {
        $page = new $viewClass( $options );
        $page->setArgs( $args );
        return $page->render();
    }

    /*
     * Render template directly.
     *
     * @param string $template template path, template name
     * @param array  $args template arguments
     *
     */
    public function render( $template , $args = array() , $engineOpts = array()  )
    {
        $view = $this->view( $engineOpts );
        $view->assign( $args );
        return $view->render( $template );
    }


    /**
     * run after
     */
    public function after() { }


    /**
     * run before
     */
    public function before() { }

    public function forbidden($msg = null)
    {
        /* XXX: dirty hack this for phpunit testing */
        if( ! CLI_MODE )
            header('HTTP/1.1 403 Forbidden');
        if( $msg ) echo $msg;
        else       echo "403 Forbidden";
        exit(0);
    }


    public function forward($class, $action, $parameters = array())
    {
        $controller = new $class;
        return $controller->runAction( $action , $parameters );
    }


    /**
     * check if the controller action exists
     *
     * @param string $action action name
     * @return boolean
     */
    public function hasAction($action)
    {
        if( method_exists($this,$action . 'Action') )
            return $action . 'Action';
        return false;
    }

    public function getDefaultActionMethod()
    {
        if( $this->hasAction('index') ) {
            return 'indexAction';
        }

        if( method_exists( $this, 'run' ) ) {
            return 'run';
        }
    }

    protected function checkActionParameters($refParameters,$arguments)
    {
        // XXX: 

    }


    /**
     * dispatch and run controller action method
     *
     * $c->dispatchAction('index', $route );  => indexAction method
     * $c->dispatchAction('post',  $route );  => postAction method
     * 
     * @param string $action action name
     *
     */
    public function runAction($action,$parameters)
    {
        $method = $this->hasAction($action);
        if( ! $method )
            $method = $this->getDefaultActionMethod();

        if( ! $method ) {
            // XXX: show exception
            header('HTTP/1.1 403');
            throw new Exception( "Controller action method $method not found." );
        }
            
        $this->before();

        // validation action method prototype
        $vars = isset($parameters['vars']) ? $parameters['vars'] : array();

        // get relection method parameter prototype for checking...
        $ro = new ReflectionObject( $this );
        $rm = $ro->getMethod($method);

        $rfParameters = $rm->getParameters();
        $arguments = array();
        foreach( $rfParameters as $rfParam ) {
            if( isset( $vars[ $rfParam->getName() ] ) ) 
            {
                $arguments[] = $vars[ $rfParam->getName() ];
            } 
            else if( isset($parameters['default'][ $rfParam->getName() ] )
                            && $default = $parameters['default'][ $rfParam->getName() ] )
            {
                $arguments[] = $default;
            }
            else {
                throw new Exception('controller parameter error');
            }
        }

        // XXX: check parameter numbers here

        $content = call_user_func_array( array( $this, $method ) , $arguments );
        $this->after();
        return $content;
    }

}

