<?php

namespace Phifty\View;

use Phifty\View\Engine;
use Phifty\View\EngineInterface;

class Smarty extends \Phifty\View\Engine
    implements \Phifty\View\EngineInterface
{

    public function newRenderer()
    {
        $smarty = new \Phifty\Bundle\Smarty;
        $smarty->template_dir = $this->getTemplateDirs();
        $smarty->compile_dir  = $this->getCachePath();

        // $opts = $this->kernel->config('View.Smarty');

        /*
        foreach( $opts as $key => $value )
            $smarty->$key = $value;
        */

        /*
        # array_push( $smarty->template_dir , $app_tpl_dir  );
        # XXX: move to config.
        $smarty->registerPlugin("modifier","loc", "smarty_modifier_loc");
        */

        return $smarty;
    }

    public function applyArgs($args = array() )
    {
        foreach( $args as $key => $value )
            $this->getRenderer()->assign( $key , $value );
    }

    public function render( $template , $args = array() )
    {
        $this->applyArgs( $args );

        return $this->getRenderer()->fetch( $template );
    }

    public function renderString( $stringTemplate , $args = array() )
    {
        $this->applyArgs( $args );

        return $this->getRenderer()->fetch( 'string:' . $stringTemplate );
    }

    public function displayString( $stringTemplate , $args = array() )
    {
        $this->applyArgs( $args );
        $this->getRenderer()->display( 'string:' . $stringTemplate );
    }

    public function display( $template , $args = array() )
    {
        $this->applyArgs( $args );

        return $this->getRenderer()->display( $template );
    }

}
