<?php

namespace Phifty\View;

use Phifty\FileUtils;
use Phifty\Action\Runner as ActionRunner;

abstract class Engine
{
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
            PH_APP_ROOT . DIRECTORY_SEPARATOR . PHIFTY_APP_DIRNAME,
            PH_ROOT . DIRECTORY_SEPARATOR . PHIFTY_APP_DIRNAME,
            PH_APP_ROOT ,
            PH_ROOT,
        );

        $configDirs = webapp()->config->get('view.template_dirs');
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
    function newRenderer()
    {

    }

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

        $dir = webapp()->config( 'view.cache_dir' );
        if( $dir )
            return $dir;

        // default cache dir
        return FileUtils::path_join( webapp()->rootDir , 'cache' );
    }

    function getTemplateDirs()
    {
        if( $this->templateDirs )
            return $this->templateDirs;

        /* default template paths */
        $paths = array();

		/* framework core view template dir */
        $frameT = webapp()->getCoreDir() . DIR_SEP . 'template';
        if( file_exists($frameT) )
            $paths[] = $frameT;

        $dirs = webapp()->config( 'view.template_dirs' );
        if( $dirs )
            foreach( $dirs as $dir )
                $paths[] = FileUtils::path_join( webapp()->rootDir , $dir );

        return $paths;
    }


    /* render method should be defined,
     * we should just call render method by default. */
    function display( $template , $args = null )
    {
        echo $this->render( $template , $args );
    }

}
