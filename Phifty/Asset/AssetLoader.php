<?php
namespace Phifty\Asset;
use Phifty\WebUtils;
use Exception;


/**
 * AssetLoader
 *
 */
class AssetLoader 
{
    static $assets = array();

    static function newAsset($name)
    {
        $class = '\Phifty\Assets\\' . $name;
        $asset = new $class;
        $asset->init();
        static::register( $asset );
        return $asset;
    }

    static function register($asset)
    {
        static::$assets[] = $asset;
    }

    static function getAsset( $name )
    {
        foreach( static::$assets as $asset ) {
            if( is_a( $asset , $name ) ) 
                return $asset;
        }
    }

    static function includeJsFiles()
    {
        // $basedir = webapp()->getWebAssetDir();
        $baseurl = '/ph/assets';
        $jsFiles = array();
        foreach( static::$assets as $asset ) {
            $files = $asset->js();
            foreach( $files as $file ) {
                // $path = $basedir    . DIRECTORY_SEPARATOR . $asset->name() . DIRECTORY_SEPARATOR . $file;
                $url = $baseurl . '/' . $asset->name() . '/' . $file;
                $jsFiles[] = $url;
            }
        }
        return WebUtils::jsTag( $jsFiles );
    }

    static function includeCssFiles()
    {
        // $basedir = webapp()->getWebAssetDir();
        $baseurl = '/ph/assets';
        $cssFiles = array();
        foreach( static::$assets as $asset ) {
            $files = $asset->css();
            foreach( $files as $file ) {
                // $path = $basedir    . DIRECTORY_SEPARATOR . $asset->name() . DIRECTORY_SEPARATOR . $file;
                $url = $baseurl . DIRECTORY_SEPARATOR . $asset->name() . DIRECTORY_SEPARATOR . $file;
                $cssFiles[] = $url;
            }
        }
        return WebUtils::cssTag($cssFiles);
    }

    static function load( $name )
    {
        $paths[] = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'assets' 
                    . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $name . '.php';

        $paths[] = PH_ROOT . DIRECTORY_SEPARATOR . 'assets' 
                    . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $name . '.php';

        foreach( $paths as $path ) {
            if( file_exists($path) ) {
                require_once $path;
                return static::newAsset( $name );
            } 
        }
        throw new Exception("Asset $name can not be loaded. $path not found.");
    }
}
