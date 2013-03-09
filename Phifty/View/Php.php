<?php

namespace Phifty\View;

use Phifty\View\Engine;
use Phifty\View\EngineInterface;

class Php
{
    public $templateDirs = array();

    public function render( $template , $args = array() )
    {
        # flush content
        ob_flush();
        ob_start();
        # $args is exported.
        require( $template );
        $content = ob_get_contents();
        ob_clean();

        return $content;
    }
}

class Php extends \Phifty\View\Engine
    implements \Phifty\View\EngineInterface
{

    public function newRenderer()
    {
        $php = new \PhpRenderer;
        $php->templateDirs = $this->getTemplateDirs();

        return $php;
    }

    public function renderString($template,$args = array())
    {
        // * try to export
        eval( $template );
    }

    public function render( $template , $args = array() )
    {
        return $this->getRenderer()->render( $template , $args );
    }

    public function display( $template , $args = array() )
    {
        echo $this->getRenderer()->render( $template , $args );
    }

}
