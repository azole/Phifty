<?php
namespace Phifty\Plugin;
use Phifty\Bundle;
use Phifty\FileUtils;
use ConfigKit\Accessor;

class Plugin extends Bundle
{
    public $config;
    public $basePath;


    public function setConfig( $config )
    {
        $this->config = $config;
    }

    public function mergeWithDefaultConfig( $config = array() )
    {
        return $this->config = array_merge( $this->defaultConfig() , $config ?: array() );
    }

    /*
    function config( $key ) 
    {
        if( isset( $this->config[ $key ] ) )
            return $this->config[ $key ];
        return null;
    }
     */

    public function defaultConfig()
    {
        return array();
    }


    /**
     * Get plugin config
     *
     * @param string $key config key
     *
     * @return mixed
     */
    public function config( $key ) 
    {
        if( isset($this->config[ $key ]) ) {
            if( is_array( $this->config[ $key ] ) )
                return new Accessor($this->config[ $key ]);
            return $this->config[ $key ];
        }

        if( strchr( $key , '.' ) !== false ) {
            $parts = explode( '.' , $key );
            $ref = $this->config;
            while( $ref_key = array_shift( $parts ) ) {
                if( ! isset($ref[ $ref_key ]) ) 
                    return null;
                    # throw new Exception( "Config key: $key not found.  '$ref_key'" );
                $ref = & $ref[ $ref_key ];
            }
            return $ref;
        }
        return null;
    }



    public function getName()
    {
        return $this->getNamespace();
    }

    public function getDir()
    {

    }

    /* static method */
    public static function locatePlugin( $name )
    {
        /* possible plugin paths */
        $paths = array();
        $paths[] = FileUtils::path_join( PH_APP_ROOT , 'plugins' , $name );
        $paths[] = FileUtils::path_join( PH_ROOT , 'plugins' , $name );
        foreach( $paths as $path )
            if( file_exists( $path ) )
                return $path;
    }

    public function getExportWebDir()
    {
        $name = $this->getName();
        return '/ph/plugins/' . $name;
        # return FileUtils::path_join( kernel()->getWebPluginDir() , $name );
    }


    /*
    Use case:
        kernel()->plugin('SB')->render( 'product/view' , array( .... args .... ) );

    */
    public function render( $vpath )
    {
        /* XXX: render view */
    }

    /* methods for definitions */

    public function init()
    {
        // $this->route( );
    }

    public function beforePage()
    {

    }
}

