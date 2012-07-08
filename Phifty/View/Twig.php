<?php
namespace Phifty\View;

use Phifty\View\Engine;
use Phifty\FileUtils;
use Phifty\ClassUtils;

use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Function_Function;

use Twig_Extension_Debug;
use Twig_Extension_Optimizer;
use Twig_Extension_Escaper;
use Twig_Loader_String;

use Twig_Extensions_Extension_Text;
use Twig_Extensions_Extension_I18n;

class Twig extends \Phifty\View\Engine 
//    implements \Phifty\View\EngineInterface
{
    public $loader;
    public $env;

    public function newRenderer()
    {
        $cacheDir = $this->getCachePath();
        $dirs     = $this->getTemplateDirs();
        $loader   = new Twig_Loader_Filesystem( $dirs );

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
        $twigConfig = $kernel->config->get( 'View.Twig' );
        if( $twigConfig && is_array( $twigConfig ) ) {
            $envOpts = array_merge( $envOpts , $twigConfig );
        }

        /* 
         * Env Options
         * http://www.twig-project.org/doc/api.html#environment-options
         * */
        $this->env = new Twig_Environment($loader, $envOpts );

        /**
         * Load extensions from config settings 
         *
         * Twig:
         *   CoreExtensions:
         *   Extensions:
         */
        if( $twigConfig ) {
            if( isset($twigConfig['CoreExtensions'] ) ) {
                foreach( $twigConfig['CoreExtensions'] as $extension ) {
                    $extname = null;
                    $options = null;
                    if( is_string($extension) ) {
                        $extname = $extension;
                    } elseif ( is_array( $extension ) ) {
                        $extname = key($extension);
                        $options = $extension[ $extname ];
                    }
                    $class = 'Twig_Extension_' . $extname;
                    $this->env->addExtension( ClassUtils::new_class( $class , $options ) );
                }
            }

            if( isset($twigConfig['Extensions'] ) ) { 
                foreach( $twigConfig['Extensions'] as $extension ) {
                    $extname = null;
                    $options = null;
                    if( is_string($extension) ) {
                        $extname = $extension;
                    } elseif ( is_array( $extension ) ) {
                        $extname = key($extension);
                        $options = $extension[ $extname ];
                    }
                    $class = 'Twig_Extensions_Extension_' . $extname;
                    $this->env->addExtension( ClassUtils::new_class( $class , $options ) );
                }
            }

        } else {
            /* Default extensions */

            /* if twig config is not define, then use our dev default extensions */
            if( $isDev ) {
                $this->env->addExtension( new Twig_Extension_Debug );
            } else {
                // for production mode
                $this->env->addExtension( new Twig_Extension_Optimizer );
            }

            $this->env->addExtension( new Twig_Extensions_Extension_Text );
            $this->env->addExtension( new Twig_Extensions_Extension_I18n );
        }
        $this->loader = $loader;
        $this->registerFunctions();
        return $this->env;
    }

    function registerFunctions()
    {
        $exports = array(
            'uniqid' => 'uniqid',
            'md5' => 'md5',
            'time' => 'time',
            'sha1' => 'sha1',
            'gettext' => 'gettext',
            '_' => '_',
            'count' => 'count',
        );
        foreach( $exports as $export => $func ) {
            $this->env->addFunction( $export , new Twig_Function_Function( $func ));
        }

        // auto-register all native PHP functions as Twig functions
        $this->env->registerUndefinedFunctionCallback(function($name) {
            // use functions with prefix 'array_' and 'str'
            if (function_exists($name) && ( strpos($name,'array_') === 0 || strpos($name,'str') === 0 ) ) {
                return new Twig_Function_Function($name);
            }
            return false;
        });
    }

    function newStringRenderer()
    {
        return new Twig_Environment( new Twig_Loader_String );
    }

    function render( $template,$args = array() )
    {
        return $this->getRenderer()->loadTemplate( $template )->render( $args );
    }

    function display( $template , $args = array() )
    {
        $this->getRenderer()->loadTemplate( $template )->display($args);
    }

    function renderString( $stringTemplate , $args = array() )
    {
        $this->newStringRenderer()->loadTemplate( $stringTemplate )->render( $args );
    }

    function displayString( $stringTemplate , $args = array() )
    {
        $this->newStringRenderer()->loadTemplate( $stringTemplate )->display($args);
    }

}
