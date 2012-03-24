<?php

namespace Phifty\View;

use Phifty\FileUtils;
use ActionKit\Runner as ActionRunner;

abstract class Engine
{
    public $kernel;
	public $options = array();
    public $templateDirs = array();
    public $cacheDir;
	public $renderer;

    /*
     * Contructor 
     *   template_dirs
     *   cache_dir
     */
    function __construct( $options = array() )
    {
        $this->kernel = kernel();

		/* save options */
		$this->options = $options;


		/* preprocess options */
        if( isset( $options['template_dirs'] ) )
            $this->templateDirs = (array) $options['template_dirs'];

        if( isset( $options['cache_dir'] ) )
            $this->cacheDir = $options['cache_dir'];

        if( empty( $this->templateDirs) ) {
            $this->templateDirs = $this->getDefaultTemplateDirs();
        }
    }

    function getDefaultTemplateDirs()
    {
        // when we move all plugins into applications, we take off the PH_APP_ROOT and PH_ROOT from paths
        $dirs = array(
            $this->kernel->rootAppDir,
            $this->kernel->frameworkAppDir,
            $this->kernel->rootPluginDir,
            $this->kernel->frameworkPluginDir,
            $this->kernel->rootDir,
            $this->kernel->frameworkDir,
        );

        $configDirs = kernel()->config->get('framework','View.TemplateDirs');
        if( $configDirs ) {
            foreach($configDirs as $dir) {
                $dirs[] = PH_APP_ROOT . '/' . $dir;
            }
        }
        return $dirs;
    }

    /*
     * Method for creating new renderer object
     */
    abstract function newRenderer();

    /*
     * Return Renderer object, statical
     */
    function getRenderer()
    {
        if( $this->renderer )
            return $this->renderer;
        return $this->renderer = $this->newRenderer();
    }

    /* refactor to Phifty\View\Smarty and Phifty\View\Twig */
    static function createEngine( $backend , $opts = array() )
    {
        switch( $backend )
        {
            case "smarty":
                return new \Phifty\View\Smarty( $opts );
                break;

            case "twig":
                return new \Phifty\View\Twig( $opts );
                break;

            case "php":
                return new \Phifty\View\Php( $opts );

            default:
                throw new \Exception("Engine type $backend is not supported.");
        }
    }

    function getCachePath()
    {
        if( $this->cacheDir )
            return $this->cacheDir;

        return kernel()->config->get( 'framework', 'View.CacheDir' )
            ?: FileUtils::path_join( kernel()->rootDir , 'cache' );
    }

    function getTemplateDirs()
    {
        if( $this->templateDirs )
            return $this->templateDirs;

        /* default template paths */
        $paths = array();

		/* framework core view template dir */
        $frameT = kernel()->app('Core')->getTemplateDir();
        if( file_exists($frameT) ) {
            $paths[] = $frameT;
        }

        if( $dirs = kernel()->config->get( 'framework', 'View.TemplateDirs' ) ) {
            foreach( $dirs as $dir )
                $paths[] = kernel()->rootDir  . DIRECTORY_SEPARATOR . $dir;
        }
        $paths[] = kernel()->rootPluginDir;
        $paths[] = kernel()->frameworkPluginDir;
        return $paths;
    }


    /* render method should be defined,
     * we should just call render method by default. */
    function display( $template , $args = null )
    {
        echo $this->render( $template , $args );
    }

}
