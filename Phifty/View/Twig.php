<?php
namespace Phifty\View;

use Phifty\View\Engine;
use Phifty\FileUtils;
use Phifty\ClassUtils;

class Twig extends \Phifty\View\Engine 
    implements \Phifty\View\EngineInterface
{
    public $twigLoader;
    public $twigEnv;

    function newRenderer()
    {
        $cacheDir = $this->getCachePath();
		$dirs = $this->getTemplateDirs();
        $loader = new \Twig_Loader_Filesystem( $dirs );

        $envOpts = array();

        $kernel = kernel();
        $isDev = $kernel->isDev;

        // get twig config
        if( $isDev ) {
            $envOpts['cache'] = $cacheDir;
            $envOpts['debug'] = true;
            $envOpts['auto_reload'] = true;
        }
        else {
            $envOpts['cache'] = $cacheDir;
        }

        /* if twig config is defined, then override the current config */
        $twigConfig = $kernel->config( 'view.twig' );
        if( $twigConfig && is_array( $twigConfig ) ) {
            $envOpts = array_merge( $envOpts , $twigConfig );
        }

        /* 
         * Env Options
         * http://www.twig-project.org/doc/api.html#environment-options
         * */
        $twig = new \Twig_Environment($loader, $envOpts );

        /* load extensions from config settings */
        if( $twigConfig ) {

            if( isset($twigConfig['core_extensions'] ) ) {
                foreach( $twigConfig['core_extensions'] as $extension ) {
                    $extname = null;
                    $options = null;
                    if( is_string($extension) ) {
                        $extname = $extension;
                    } elseif ( is_array( $extension ) ) {
                        $extname = key($extension);
                        $options = $extension[ $extname ];
                    }
                    $class = '\Twig_Extension_' . $extname;
                    $ext = ClassUtils::new_class( $class , $options );
                    $twig->addExtension($ext);
                }
            }

            if( isset($twigConfig['extensions'] ) ) { 
                foreach( $twigConfig['extensions'] as $extension ) {
                    $extname = null;
                    $options = null;
                    if( is_string($extension) ) {
                        $extname = $extension;
                    } elseif ( is_array( $extension ) ) {
                        $extname = key($extension);
                        $options = $extension[ $extname ];
                    }
                    $class = '\Twig_Extensions_Extension_' . $extname;
                    $ext = ClassUtils::new_class( $class , $options );
                    $twig->addExtension($ext);
                }
            }

        } else {
            /* Default extensions */

            /* if twig config is not define, then use our dev default extensions */
            if( $isDev ) {
                $debug = new \Twig_Extensions_Extension_Debug;
                $twig->addExtension( $debug );
            } else {
                // for production
                $optiz = new \Twig_Extension_Optimizer;
                $twig->addExtension( $optiz );
            }

            $text = new \Twig_Extensions_Extension_Text;
            $twig->addExtension( $text );

            $i18n = new \Twig_Extensions_Extension_I18n;
            $twig->addExtension( $i18n );
        }

        $this->twigEnv = $twig;
        $this->twigLoader = $loader;
        $this->registerFunctions( $twig );
        return $twig;
    }

    function registerFunctions( $twig )
    {
        $twig->addFunction('uniqid', new \Twig_Function_Function('uniqid'));
        $twig->addFunction('md5', new \Twig_Function_Function('md5'));
        $twig->addFunction('time', new \Twig_Function_Function('time'));
        $twig->addFunction('sha1', new \Twig_Function_Function('sha1'));
        $twig->addFunction('gettext', new \Twig_Function_Function('gettext'));
    }

    function newStringRenderer()
    {
        $loader = new \Twig_Loader_String();
        $twig = new \Twig_Environment($loader);
        return $twig;
    }

    function render( $template,$args = array() )
    {
        $template = $this->getRenderer()->loadTemplate( $template );
        return $template->render( $args );
    }

    function display( $template , $args = array() )
    {
        $template = $this->getRenderer()->loadTemplate( $template );
        $template->display( $args );
    }

    function renderString( $stringTemplate , $args = array() )
    {
        $twig = $this->newStringRenderer();
        $template = $twig->loadTemplate( $stringTemplate );
        return $template->render( $args );
    }

    function displayString( $stringTemplate , $args = array() )
    {
        $twig = $this->newStringRenderer();
        $template = $twig->loadTemplate( $stringTemplate );
        $template->display( $args );
    }

}
