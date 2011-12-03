<?php
namespace Phifty;
use Phifty\Web\Request;
use Phifty\YAML;
use Exception;

interface ControllerInterface 
{
    public function after();
    public function before();

    function post( $env );
    function get( $env );
}

class Controller 
    implements ControllerInterface
{

    /* env request object, handles post, get, request objects */
    public $env;

    /* route data */
    public $route;

    function __construct( $route = null )
    {
        $this->route = $route;
        $this->env   = new Request;
        $this->init();
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
    function redirect($path)
    {
        header( 'Location: ' . $path );
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

    public function getRoute()
    {
        return $this->route;
    }

    public function after() { }

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


}

