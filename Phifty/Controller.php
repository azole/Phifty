<?php
namespace Phifty;
use Phifty\Web\Request;
use Universal\Http\HttpRequest;
use ReflectionObject;
use SerializerKit;
use SerializerKit\Serializer;
use SerializerKit\YamlSerializer;
use SerializerKit\XmlSerializer;
use Exception;

/*
    Synopsis
    $controller = new $class( $this );
*/

class Controller extends \Roller\Controller
{
    /* env request object, handles post, get, request objects */
    public $env;
    public $request;

    public function init()
    {
        $this->env   = new Request;
        $this->request = new HttpRequest;
    }

    public function getCurrentUser()
    {
        return kernel()->currentUser;
    }

    /**
     * xxx: is not used yet.
     *
     * currentUserCan method
     *
     * provide a permission check.
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

        $templateEngine = kernel()->config->get('framework','View.Backend') ?: 'twig';
        $viewClass      = kernel()->config->get('framework','View.Class') ?: 'Phifty\View';
        $engine = \Phifty\View\Engine::createEngine( $templateEngine , $options );
        return $view = new $viewClass( $engine );  // pass 'Smarty' or 'Twig'
    }

    /* web utils functions */
    function redirect($url)
    {
        header( 'Location: ' . $url );
    }

    function redirectLater($url,$seconds = 1 )
    {
        header( "refresh: $seconds; url=" . $url );
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
        $yaml = new YamlSerializer;
        return $yaml->encode($data);
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

    public function forbidden($msg = null)
    {
        /* XXX: dirty hack this for phpunit testing */
        if( ! CLI_MODE )
            header('HTTP/1.1 403 Forbidden');
        if( $msg ) echo $msg;
        else       echo "403 Forbidden";
        exit(0);
    }


    /**
     * forward to another controller
     *
     *
     *  return $this->forward( '\OAuthPlugin\Controller\AuthenticationErrorPage','index',array(
     *      'vars' => array(
     *          'message' => $e->lastResponse
     *      )
     *  ));
     */
    public function forward($class, $action = 'index' , $parameters = array())
    {
        // $controller = new $class;
        // xxx: implement this
        // return $controller->runAction( $action , $parameters );
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

}
