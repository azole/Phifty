<?php
namespace Phifty\Plugin;
use Phifty\Bundle;
use Phifty\FileUtils;

class Plugin extends Bundle
{
    public $basePath;

    public function getName()
    {
        return $this->getNamespace();
    }

    /* static method */
    public static function locatePlugin( $name )
    {
        /* possible plugin paths */
        $paths = array();
        $paths[] = FileUtils::path_join( PH_APP_ROOT , 'plugins' , $name );
        $paths[] = FileUtils::path_join( PH_ROOT , 'plugins' , $name );
        foreach( $paths as $path ) {
            if ( file_exists( $path ) ) {
                return $path;
            }
        }
    }

    /*
    Use case:
        kernel()->plugin('SB')->render( 'product/view' , array( .... args .... ) );

    */
    public function render( $vpath )
    {
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
