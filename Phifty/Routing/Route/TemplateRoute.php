<?php
namespace Phifty\Routing\Route;
use Phifty\View\Engine;
use Phifty\Routing\Route;

class TemplateRoute extends Route
{
    function evaluate() 
    {
        $template   = $this->get('template');
        $args       = $this->get('args');
        $engineType = $this->get('engine');

        if( ! $engineType )
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


