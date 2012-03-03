<?php
namespace Phifty\Routing;
use Phifty\View\Engine;
use Roller\Controller;

class TemplateController extends Controller
{
    public $template;
    public $args;

    public function __construct($args) 
    {
        $this->template = $args['template'];
        $this->args = @$args['args'];
    }

    function run()
    {
        $template   = $this->template;
        $args       = $this->args;
        $engineType = webapp()->config('view.backend');

        /* get template engine */
        $engine = Engine::createEngine( $engineType );
        $viewClass = webapp()->config('view.class');
        if( ! $viewClass )
            $viewClass = '\Phifty\View';

        $view = new $viewClass( $engine );
        if( $args )
            $view->assign( $args );
        return $view->render( $template );
    }
}


