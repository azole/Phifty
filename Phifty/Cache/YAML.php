<?php

namespace Phifty\Cache;
use Spyc;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use Exception;

/*
    $yCache = new YAML( array( 'enable_apc' => 1 , 'cache_dir' => 'cache' ) );
    $data = $yCache->loadFile( 'config.yml' );
 */
class YAML
{
    public $cacheDir;
    function __construct( $options = array() )
    {
        if( ! isset($options['cache_dir']) )
            throw new Exception( 'cache_dir is not defined.' );

        $this->cacheDir = $options['cache_dir'];
        if( ! file_exists( $this->cacheDir ) )
            mkdir( $this->cacheDir , 0777, true );
    }

    function getCacheDir()
    {
        return $this->cacheDir;
    }

    function getCachePath($filePath)
    {
        return $this->cacheDir . DIRECTORY_SEPARATOR . str_replace( DIRECTORY_SEPARATOR , '_' , $filePath ) . '.cache';
    }

    function hasCache($filePath)
    {
        $cacheFile = $this->getCachePath( $filePath );
        return ( file_exists( $cacheFile ) 
            && ( filemtime( $cacheFile ) >= filemtime( $filePath ) ) );
    }

    function saveCache( $filePath , $data )
    {
        $cacheFile = $this->getCachePath( $filePath );
        file_put_contents( $cacheFile, serialize( $data ) );
    }

    function loadCache( $filePath )
    {
        $cacheFile = $this->getCachePath( $filePath );
        return unserialize( file_get_contents( $cacheFile ) );
    }


    /*
     * truncate cache
     */
    function truncate()
    {
        $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->cacheDir),
                    RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($iterator as $file) {
            if ($file->isFile())
                unlink( $file->getPathname() );
        }
    }

    function loadFile( $filePath )
    {
        /* if Cache file is newer */
        if( $this->hasCache($filePath) )
        {
            return $this->loadCache($filePath);
        }

        $data = Spyc::YAMLLoad($filePath);
        $this->saveCache( $filePath , $data );
        return $data;
    }
}


