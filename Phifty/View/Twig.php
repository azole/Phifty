<?php
namespace Phifty\View;

use Phifty\View\Engine;
use Phifty\ClassUtils;

use AssetToolkit\Extension\Twig\AssetExtension;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Function_Function;

use Twig_Extension_Debug;
use Twig_Extension_Optimizer;
use Twig_Loader_String;

use Twig_Extensions_Extension_Text;
use Twig_Extensions_Extension_I18n;

/**
 * Rewrite this as an extension.
 *
 * {% set obj = new('InputSystem\\Model\\Patient') %}
 * {% set obj = new('InputSystem\\Model\\PatientSchema') %}
 */
function newObject($class)
{
    $args = func_get_args();
    array_shift($args);
    return \Phifty\ClassUtils::new_class($class,$args);
}

class Twig extends \Phifty\View\Engine
//    implements \Phifty\View\EngineInterface
{
    public $loader;
    public $env;

    public function newRenderer()
    {
        $kernel = kernel();
        $this->env = $kernel->twig->env;
        $this->loader = $kernel->twig->loader;
        return $this->env;
    }

    public function newStringRenderer()
    {
        return new Twig_Environment( new Twig_Loader_String );
    }

    public function render( $template,$args = array() )
    {
        return $this->getRenderer()->loadTemplate( $template )->render( $args );
    }

    public function display( $template , $args = array() )
    {
        $this->getRenderer()->loadTemplate( $template )->display($args);
    }

    public function renderString( $stringTemplate , $args = array() )
    {
        return $this->newStringRenderer()->loadTemplate( $stringTemplate )->render( $args );
    }

    public function displayString( $stringTemplate , $args = array() )
    {
        $this->newStringRenderer()->loadTemplate( $stringTemplate )->display($args);
    }
}
