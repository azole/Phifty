<?php
namespace Phifty;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class FileUtils {

    public static function pretty_size($bytes)
    {
        if( $bytes < 1024 )
            return $bytes . 'B';
        if( $bytes < 1024 * 1024 )
            return ((int) $bytes / 1024) . 'KB';
        if( $bytes < 1024 * 1024 * 1024 )
            return ((int) $bytes / 1024 / 1024) . 'MB';
        if( $bytes < 1024 * 1024 * 1024 * 1024 )
            return ((int) $bytes / 1024 / 1024 / 1024) . 'GB';
        return ((int) $bytes / 1024 / 1024) . 'MB';
    }

    public static function path_join($list)
    {
        $args = null;
        if( is_array($list) ) {
            $args = $list;
        } else {
            $args = func_get_args();
        }
        return call_user_func(  'join' , DIRECTORY_SEPARATOR , $args );
    }

    static function mkdir( $path , $verbose = false , $mode = 0777 )
    {
        if( $verbose )
            echo "Creating dir: $path\n";
        if( false === file_exists($path) )
            mkdir($path,$mode,true);
    }

    static function rmtree( $paths , $verbose = false )
    {
        $paths = (array) $paths;
        foreach( $paths as $path ) {

            if( ! file_exists( $path ) )
                die( "$path does not exist." );

            if( is_dir( $path ) ) 
            {
                $iterator = new \DirectoryIterator($path);
                foreach ($iterator as $fileinfo) 
                {
                    if( $fileinfo->isDir() ) {
                        if( $fileinfo->getFilename() == "." )
                            continue;

                        if( $fileinfo->getFilename() == ".." )
                            continue;
                        self::rmtree( $fileinfo->getPathname() );

                        if( $verbose )
                            echo "\trmdir: " . $fileinfo->getPathname() . "\n";
                    }
                    elseif ($fileinfo->isFile()) {
                        if( $verbose )
                            echo "\tunlink file: " . $fileinfo->getPathname() . "\n";
                    
                        if( unlink( $fileinfo->getPathname() ) == false )
                            die( "File delete error: {$fileinto->getPathname()}" );
                    }
                }
                rmdir( $path );
            } 
            elseif( is_file( $path ) ) {
                unlink( $path );
            }


        }

    }

    static function mkpath( $paths , $verbose = false , $mode = 0777 )
    {
        $paths = (array) $paths;
        foreach( $paths as $path ) {
            if( $verbose )
                echo "\tCreating directory $path\n";
            if( file_exists( $path ) )
                continue;
            mkdir( $path, $mode , true );  // recursive
        }
    }

    static function create_keepfile( $path )
    {
        $keepfile = static::path_join( $path , '.keep' );
        touch( $keepfile );
    }

    /* substract cwd path */
    static function relative_path( $abspath ) 
    {
        $path = realpath( $abspath );
        $cwd = getcwd();
        return substr( $path , strlen( $cwd ) + 1 );
    }

    /* remove base path , return relative path */
    static function remove_base( $path , $base )
    {
        return substr( $path , strlen( $base ) + 1 );
    }

    static function expand_path( $path ) 
    {
        $start = strpos( $path , '{' );
        $end   = strpos( $path , '}' , $start );

        if( $start === false || $end === false )
            return (array) $path;

        $expand = explode(',',substr( $path , $start + 1 , $end - $start - 1 ));
        $wstr_start = substr( $path , 0 , $start  );
        $wstr_end   = substr( $path , $end + 1 );
        $paths = array();
        foreach( $expand as $item )
        {
            $paths[] = $wstr_start . $item . $wstr_end;
        }
        return $paths;
    }

    /* 
     * Expand dir to file paths
     *
     * Return file list with fullpath.
     * */
    static function expand_dir($dir)
    {
        if( is_dir($dir) ) {
            $files = array();
            $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir),
                                                    \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $path) {
                if ($path->isDir()) {
                    # rmdir($path->__toString());
                } elseif( $path->isFile() ) { 
                    array_push( $files , $path->__toString() );
                }
            }
            return $files;
        }
        return array($dir);
    }




    static function concat_files( $files )
    {
        $content = '';
        foreach( $files as $file ) {
            $content .= file_get_contents( $file );
        }
        return $content;
    }

    static function filename_append_md5( $filename , $filePath = null )
    {
        $suffix = $filePath ? md5( $filePath ) : md5( time() );
        $pos = strrpos( $filename , '.' );
        if( $pos ) {
            return 
                substr( $filename , 0 , $pos )
                . $suffix 
                . substr( $filename , $pos );
        }
        return $filename . $suffix;
    }

    public static function filename_increase($path) 
    {
        if( ! file_exists($path) )
            return $path;

        $pos = strrpos( $path , '.' );
        if( $pos !== false ) {
            $filepath = substr($path, 0 , $pos);
            $extension = substr($path, $pos);
            $newfilepath = $filepath . $extension;
            $i = 1;
            while( file_exists($newfilepath) ) {
                $newfilepath = $filepath . " (" . $i++ . ")" . $extension;
            }
            return $newfilepath;
        }
        return $path;
    }

    public static function filename_suffix( $filename , $suffix )
    {
        $pos = strrpos( $filename , '.' );
        if( $pos !== false ) {
            return 
                substr( $filename , 0 , $pos )
                . $suffix 
                . substr( $filename , $pos );
        }
        return $filename . $suffix;
    }

    static function mimetype( $file )
    {
        $fi = new \finfo( FILEINFO_MIME );
        $info = $fi->buffer(file_get_contents($file));
        $attrs = explode(';',$info);
        return $attrs[0];
    }

    static function is_cache_expired( $cacheFile , $targetFile )
    {
        return filemtime($targetFile) > filemtime($cacheFile);
    }
}


