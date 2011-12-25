<?php
namespace Phifty\Asset;
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
        // xxx: use from WebPath
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

    function css() { return array(); }
    function js() { return array();  }
}


