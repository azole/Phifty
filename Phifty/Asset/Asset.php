<?php
namespace Phifty\Asset;

use Phifty\View\TwigLight;
use ReflectionObject;

abstract class Asset 
{
	public $template;

    function name()
    {
        $class = get_class($this);
        $pos = strrpos( $class , '\\' ) + 1;
        if( $pos !== false )
            return substr( $class, $pos );
        return $class;
    }

    function init() 
    {

    }

    function baseDir()
    {
        $object = new ReflectionObject($this);
        return dirname($object->getFilename());
    }

    function baseUrl()
    {
        return '/ph/assets/' . $this->name();
    }

    // return minified js content.
    function minified_js() 
    {

    }

    // return minified css content.
    function minified_css()
    {

    }


    function getTemplate() 
    {
        if( $this->template )
            return $this->template;
        return $this->template();
    }

    function setTemplate( $template )
    {
        $this->template = $template;
    }

    function template() 
    {

    }

    function render() 
    {
        $twig = TwigLight::getEngine();
        $template_content = $this->getTemplate();
        $template = $twig->loadTemplate( $template_content );
        return $template->render(array( 'Asset' => $this ));
    }

    function css() { return array(); }
    function js() { return array();  }
}


