<?php

namespace Phifty\Web;

/*
	$loader = new Phifty\Web\Loader(array( 
		"root_dir" => ...
		"cache_dir" => "framework/res/cache"
		"base_url" => "ph", // link to framework/res
		"minify"  => false
   	));

	$loader->import( "action" , array(
		"base" => '...'
		"js" =>  array( "framework/" ... )
		"css" => array( "path/to/dir" )
	));

	echo $loader->getHtml();
*/

use Phifty\Minify\JSMin;
use Phifty\Minify\CssMin;

class Loader {

	var $rootDir;
	var $cacheDir;
	var $minify;

    /* js & css files bundles */
	var $bundles = array();

	var $jsFiles = array();
	var $cssFiles = array();


	function __construct( $config ) 
	{
		$config = (object) $config;
		$this->rootDir = @$config->root_dir;
		# $this->baseDir = @$config->base_dir;
		$this->baseUrl = (string) @$config->base_url;
		$this->cacheDir = @$config->cache_dir;
		$this->minify   = @$config->minify;
	}


    function includeJs( $path ) 
    {
        $path = $this->getRelativePath( $path );
        return '<script type="text/javascript" src="' . $path . '"></script>' . "\n";
    }

    function includeCss( $path , $media = "screen" ) 
    {
        $path = $this->getRelativePath( $path );
        return '<link rel="stylesheet" type="text/css" href="' . $path . '" media="' . $media . '">' . "\n";
    }

	function import( $options )
	{
		$jsPaths = (array) @$options['js'];
		$cssPaths = (array) @$options['css'];

        $name = @$options['name'];

		if( ! $jsPaths ) $jsPaths = array();
		if( ! $cssPaths ) $cssPaths = array();


		$jsFiles = array();
		$cssFiles = array();

		foreach( $jsPaths as $path )
			$jsFiles[] = $this->expand($path);

		foreach( $cssPaths as $path )
			$cssFiles[] = $this->expand($path);

        if( ! $name ) 
            $name = 'bundle' . count($this->bundles);

        $this->bundles[] = 
                (object) array_merge( $options , array(
			"name" => $name,
			"js"   => $jsFiles,
			"css"  => $cssFiles
	   	));
	}

	function expand($dir)
	{
		if( is_dir($dir) ) {
			$files = array();
			$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir),
													RecursiveIteratorIterator::CHILD_FIRST);
			foreach ($iterator as $path) {
				if ($path->isDir()) {
					# rmdir($path->__toString());
				} else {
					array_push( $files , $path->__toString() );
				}
			}
			return $files;
		}
		return array($dir);
	}


	function getRelativePath( $path )
	{

        if( strstr( $path , PH_APP_ROOT ) ) {
            /* remove path from root(/) to web/ */
            return substr_replace( $path , "" , 0 , 
                    strlen( PH_APP_ROOT . '/web/' ) );
        }
        return $path;
	}



    function isUpdateBundleRequired($b,$cache_ts)
    {
        if( @$b->force )
            return true;

		foreach( @$b->js as $files ) {
			foreach( $files as $file ) {
                if( filemtime($file) > $cache_ts )
                    return true;
            }
		}
		foreach( @$b->css as $files ) {
			foreach( $files as $file ) {
                if( filemtime($file) > $cache_ts )
                    return true;
            }
		}
        return false;
    }

	function minifyBundle($b) 
	{
        $lastModify = 0;
		$jsContent = "";
		$cssContent = "";
		foreach( @$b->js as $files ) {
			foreach( $files as $file ) {
                $mtime = filemtime( $file );
                $lastModify = $mtime > $lastModify ? $mtime : $lastModify;
				$jsContent .= file_get_contents( $file );
            }
		}
		foreach( @$b->css as $files ) {
			foreach( $files as $file ) {
                $mtime = filemtime( $file );
                $lastModify = $mtime > $lastModify ? $mtime : $lastModify;
				$cssContent .= file_get_contents( $file );
            }
		}

        /*
		$css = CssMin::minify( $cssContent );
         */
		$css = CssMin::minify( $cssContent, array(
			"remove-empty-blocks"           => false,
			"remove-empty-rulesets"         => false,
			"remove-last-semicolons"        => false,
			"convert-css3-properties"       => true,
			"convert-font-weight-values"    => true, // new in v2.0.2
			"convert-named-color-values"    => true, // new in v2.0.2
			"convert-hsl-color-values"      => true, // new in v2.0.2
			"convert-rgb-color-values"      => true, // new in v2.0.2; was "convert-color-values" in v2.0.1
			"compress-color-values"         => true,
			"compress-unit-values"          => true,
			"emulate-css3-variables"        => true
		) );

		$js  = JSMin::minify( $jsContent );
        return array( 
            "css"  => $css, 
            "js"   => $js,
            "time" => $lastModify
        );
	}


    function getCachePath( $fn )
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . $fn;
    }

    function saveCache( $path , $content )
    {
        file_put_contents( $path , $content ); 
    }


	function render() 
	{
		$html = "";

        /* minify all */
		if( $this->minify )
		{
            $jsCacheFn = 'cache.js';
            $cssCacheFn = 'cache.css';

            /* get cache time stamp */
            $jspath = $this->getCachePath( $jsCacheFn );
            $csspath = $this->getCachePath( $cssCacheFn );
            $jsCacheTs  = file_exists( $jspath ) ? filemtime( $jspath ) : 0;
            $cssCacheTs = file_exists( $jspath ) ? filemtime( $csspath ) : 0;


            $doUpdateCache = false;
            $cacheTs = max( $jsCacheTs , $cssCacheTs );
            foreach( $this->bundles as $b ) {
                if( $this->isUpdateBundleRequired( $b , $cacheTs ) ) {
                    $doUpdateCache = true;
                    break;
                }
            }

            if( $doUpdateCache ) {
                // preload
                $js = "";
                $css = "";
                $lastts = 0;
                foreach( $this->bundles as $b ) {
                    $ret = $this->minifyBundle( $b );
                    $js .= $ret['js'];
                    $css .= $ret['css'];

                    $lastts = $ret['time'] > $lastts ?
                        $ret['time'] : $lastts;
                }

                // now we should save the cache
                $this->saveCache( $csspath , $css );
                $this->saveCache( $jspath , $js );

                $html .= "<!-- js/css minified cache rebuilded. -->\n";
            }

            $html .=  $this->includeCss( $csspath ) 
                    . $this->includeJs( $jspath );
            return $html;

		} else {


			foreach( $this->bundles as $b ) {
				list($from,$to) = @$b->mapping;

                if( @$b->minify ) {

                    $jspath = $this->getCachePath(  $b->name . ".js" );
                    $csspath = $this->getCachePath( $b->name . ".css" );

                    if( file_exists($jspath) )
                        $jsts = filemtime($jspath);
                    if( file_exists($csspath) )
                        $cssts = filemtime($csspath);

                    $cachets = max( @$jsts , @$cssts );

                    if( $this->isUpdateBundleRequired( $b , $cachets ) ) {
                        $ret = $this->minifyBundle( $b );
                        $js  = $ret['js'];
                        $css = $ret['css'];

                        if( $js )
                            $this->saveCache( $csspath , $css );
                        if( $css )
                            $this->saveCache( $jspath, $js );
                    }

                    $html .= "\n<!-- bundle $b->name start -->\n";

                    if( @$b->css )
                        $html .= $this->includeCss( $csspath );
                    if( @$b->js )
                        $html .= $this->includeJs( $jspath );

                    $html .= "<!-- bundle $b->name end -->\n";

                }
                else {

                    foreach( @$b->js as $files ) {
                        foreach( $files as $file ) {
                            $file = substr_replace( $file , $to , 0 , strlen( $from ) );
                            $html .= $this->includeJs( $this->baseUrl . $file );
                        }
                    }

                    foreach( @$b->css as $files ) {
                        foreach( $files as $file ) {
                            $file = substr_replace( $file , $to , 0 , strlen( $from ) );
                            $html .= $this->includeCss( $this->baseUrl . $file );
                        }
                    }
                }
			}
			return $html;
		}


	}
}




?>
