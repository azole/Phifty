<?php
namespace Phifty\Asset;
use DirectoryIterator;


/**
 * AssetFinder
 *
 * to export asset links
 */
class AssetFinder 
{

    static function scanAsset( $dir )
    {
        $assets = array();
        $iterator = new DirectoryIterator($dir);
        foreach ($iterator as $fileInfo) {
            if( $fileInfo->isDot() )
                continue;
            if( $fileInfo->isDir() ) {
                $name  = $fileInfo->getFilename();
                $path = $fileInfo->getPathname();
                $assets[ $name ] = (object) array( 
                    'name' => $name,
                    'web'  => $path . DIRECTORY_SEPARATOR . 'web',
                    'path' => $path,
                );
            }
        }
        return $assets;
    }

    static function findAll()
    {
        $assets = array();
        $coreAssetDir = PH_ROOT . DIRECTORY_SEPARATOR . 'assets';
        if( is_dir( $coreAssetDir ) ) {
            $assets = array_merge( $assets , self::scanAsset( $coreAssetDir ) );
        }
        $appAssetDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'assets';
        if( is_dir( $appAssetDir ) ) {
            $assets = array_merge( $assets , self::scanAsset( $appAssetDir ) );
        }
        return $assets;
    }

    static function locate( $name )
    {
        $appAssetDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'assets';

        $dir = $appAssetDir . DIRECTORY_SEPARATOR . $name ;
        if( file_exists($dir) ) 
            return (object) array(
                'name' => $name,
                'path'  => $dir,
                'web'  => $dir . DIRECTORY_SEPARATOR . 'web',
            );

        $coreAssetDir = PH_ROOT . DIRECTORY_SEPARATOR . 'assets';
        $dir = $coreAssetDir . DIRECTORY_SEPARATOR . $name ;
        if( file_exists($dir) ) 
            return (object) array(
                'name' => $name,
                'path'  => $dir,
                'web'  => $dir . DIRECTORY_SEPARATOR . 'web',
            );

        return null;
    }
}

