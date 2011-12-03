<?php

namespace Phifty;
use Phifty\WebUtils;
use Exception;

class WidgetLoader 
{
    static $widgets = array();

    static function newWidget($name)
    {
        $class = '\Phifty\Widgets\\' . $name;
        $widget = new $class;
        $widget->init();
        static::register( $widget );
        return $widget;
    }

    static function register($widget)
    {
        static::$widgets[] = $widget;
    }

    static function getWidget( $name )
    {
        foreach( static::$widgets as $widget ) {
            if( is_a( $widget , $name ) ) 
                return $widget;
        }
    }

    static function includeJsFiles()
    {
        $basedir = webapp()->getWebWidgetDir();
        $baseurl = '/ph/widgets';
        $jsFiles = array();
        foreach( static::$widgets as $widget ) {
            $files = $widget->js();
            foreach( $files as $file ) {
                $path = $basedir    . DIRECTORY_SEPARATOR . $widget->name() . DIRECTORY_SEPARATOR . $file;
                $url = $baseurl . '/' . $widget->name() . '/' . $file;
                $jsFiles[] = $url;
            }
        }
        return WebUtils::jsTag( $jsFiles );
    }

    static function includeCssFiles()
    {
        $basedir = webapp()->getWebWidgetDir();
        $baseurl = '/ph/widgets';
        $cssFiles = array();
        foreach( static::$widgets as $widget ) {
            $files = $widget->css();
            foreach( $files as $file ) {
                $path = $basedir    . DIRECTORY_SEPARATOR . $widget->name() . DIRECTORY_SEPARATOR . $file;
                $url = $baseurl . DIRECTORY_SEPARATOR . $widget->name() . DIRECTORY_SEPARATOR . $file;
                $cssFiles[] = $url;
            }
        }
        return WebUtils::cssTag($cssFiles);
    }

    static function load( $name )
    {
        $paths[] = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'widgets' 
                    . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $name . '.php';

        $paths[] = PH_ROOT . DIRECTORY_SEPARATOR . 'widgets' 
                    . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $name . '.php';

        foreach( $paths as $path ) {
            if( file_exists($path) ) {
                require_once $path;
                return static::newWidget( $name );
            } 
        }

        throw new Exception("Widget $name can not be loaded. $path not found.");
    }


}



