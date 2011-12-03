<?php

namespace Phifty\View;

use Phifty\View\Engine;
use Phifty\View\EngineInterface;
use Phifty\FileUtils;


class PhpRenderer
{
    public $templateDirs = array();

    function render( $template , $args = array() )
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

    function newRenderer()
    {
        $php = new \PhpRenderer;
        $php->templateDirs = $this->getTemplateDirs();
        return $php;
    }

    function renderString($template,$args = array()) 
    {
        // * try to export 
        eval( $template );
    }

    function render( $template , $args = array() )
    {
        return $this->getRenderer()->render( $template , $args );
    }

    function display( $template , $args = array() )
    {
        echo $this->getRenderer()->render( $template , $args );
    }

}



?>
