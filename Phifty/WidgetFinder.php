<?php

namespace Phifty;


use DirectoryIterator;

class WidgetFinder 
{

    static function scanWidget( $dir )
    {
        $widgets = array();
        $iterator = new DirectoryIterator($dir);
        foreach ($iterator as $fileInfo) {
            if( $fileInfo->isDot() )
                continue;
            if( $fileInfo->isDir() ) {
                $name  = $fileInfo->getFilename();
                $path = $fileInfo->getPathname();
                $widgets[ $name ] = (object) array( 
                    'name' => $name,
                    'web'  => $path . DIRECTORY_SEPARATOR . 'web',
                    'path' => $path,
                );
            }
        }
        return $widgets;
    }

    static function findAll()
    {
        $widgets = array();
        $coreWidgetDir = PH_ROOT . DIRECTORY_SEPARATOR . 'widgets';
        if( is_dir( $coreWidgetDir ) ) {
            $widgets = array_merge( $widgets , self::scanWidget( $coreWidgetDir ) );
        }
        $appWidgetDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'widgets';
        if( is_dir( $appWidgetDir ) ) {
            $widgets = array_merge( $widgets , self::scanWidget( $appWidgetDir ) );
        }
        return $widgets;
    }

    static function locate( $name )
    {
        $appWidgetDir = PH_APP_ROOT . DIRECTORY_SEPARATOR . 'widgets';

        $dir = $appWidgetDir . DIRECTORY_SEPARATOR . $name ;
        if( file_exists($dir) ) 
            return (object) array(
                'name' => $name,
                'path'  => $dir,
                'web'  => $dir . DIRECTORY_SEPARATOR . 'web',
            );

        $coreWidgetDir = PH_ROOT . DIRECTORY_SEPARATOR . 'widgets';
        $dir = $coreWidgetDir . DIRECTORY_SEPARATOR . $name ;
        if( file_exists($dir) ) 
            return (object) array(
                'name' => $name,
                'path'  => $dir,
                'web'  => $dir . DIRECTORY_SEPARATOR . 'web',
            );

        return null;
    }
}

