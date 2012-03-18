<?php

namespace Phifty;
use Phifty\FileUtils;
use ReflectionClass;

/* a way to locate app model,controller,view ..etc class */
class AppClassKit
{
    static function detectPluginPath( $pluginName )
    {
        /* check if it's in app first */
        $appPluginDir = PH_APP_ROOT . DIR_SEP . 'plugins' . DIR_SEP . $pluginName;
        if( file_exists( $appPluginDir ) )
            return $appPluginDir;

        /* or in core ? */
        $corePluginDir = PH_ROOT . DIR_SEP . 'plugins' . DIR_SEP . $pluginName;
        if( file_exists( $corePluginDir ) )
            return $corePluginDir;

        return null;
    }

    static function pluginPaths()
    {
        $result = array();
        $list = webapp()->pluginList();

        foreach( $list as $name ) {
            $path = static::detectPluginPath( $name );
            if( $path )
                $result[] = $path;
        }
        return $result;
    }

    static function loadDir( $dir )
    {
        if( file_exists($dir ) ) {
            $files = FileUtils::expand_Dir( $dir );
            foreach( $files as $file ) {
                $code = file_get_contents($file);
                if( strpos( $code , 'SchemaDeclare' ) !== false )
                    require_once $file;
            }
        }
    }


    /* return core Model classes */
    static function loadCoreModels()
    {
        $dir = webapp()->getCoreDir();
        $modelDir = $dir . DIRECTORY_SEPARATOR . 'Model';
        static::loadDir( $modelDir );
    }

    static function loadPluginModels()
    {
        $paths = static::pluginPaths();
        foreach( $paths as $path ) {
            static::loadDir( $path . DIR_SEP . 'Model' );
        }
    }

    /* get declared model classes */
    static function modelClasses()
    {
        $classes = get_declared_classes();
        $classes = array_filter( $classes , function($c) {
            // $rf = new ReflectionClass($c);
            // var_dump( is_a( $c, 'Lazy\Schema\SchemaDeclare' ) ); // && ! $ref->isAbstract();
            return is_a( $c, '\LazyRecord\Schema\SchemaDeclare' ); // && ! $ref->isAbstract();
        });
        return $classes;
    }

}
