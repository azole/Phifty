<?php


namespace Phifty;


/* XXX: depreacated */
/*
class CacheFileSet {
    var $files = array();
    var $expiry = 3600;
    var $cacheFile = 'cache_file';
	var $cacheDir;

    function __construct( $config = array() ) {
		$this->cacheDir = @$config['cache_dir'];
		if( ! $this->cacheDir )
			$this->cacheDir = 'cache';
    }

    function isExpired() {
        return $this->mtime_expired();
    }

    function hasCache() {
        return file_exists( $this->getCacheFilepath() );
    }

    function saveCache( $content ) {
        file_put_contents( $this->getCacheFilepath() , $content );
    }

    function getCacheUrl() { 
        return BASE_PATH . $this->cache_file;
    }

    function getCacheFilepath() { 
        return APPDIR . $this->cache_file;
    }

    function addFile( $file ) {
        array_push( $this->files, $file );
    }

    function cacheMtime() { 
        return filemtime( $this->getCacheFilepath() );
    }

    function output( $minify ) { 
        if( $minify ) {
            $this->genFilesetCache();
            return $this->outputTag( $this->getCacheUrl() );
        } else {
            $output = '';
            foreach( $this->files as $f )
                $output .= $this->outputTag( BASE_PATH . $f );
            return $output;
        }
    }

    function mtime_expired() {
        $cache_mtime = $this->cache_mtime();
        foreach( $this->files as $f ) {
            if( filemtime( APPDIR . $f ) > $cache_mtime )
                return true;
        }
        return false;
    }

    function genFilesetCache() {
        if( ! $this->dev_mode && $this->hasCache() )
            return;
        if( $this->hasCache() && ! $this->isExpired() )
            return;
        $cache_content = "";
        foreach( $this->files as $file )
            $cache_content .= $this->compressor( $file );
        $this->saveCache( $cache_content );
    }

}

 */

?>
