<?php

namespace Phifty;

use Phifty\FileUtils;


/*
 * $codegen = new CodeTemplate;
 * $codegen->render( 'app.php' , array( ... ) );
 *
 *
 * xxx: use twig instead.
 */

class CodeTemplate 
{
    public $kernel;
    public $smarty;

    public function __construct()
    {
        $this->kernel = \AppKernel::getInstance();
        $this->smarty = $this->getSmarty();
    }

    public function getCodebaseDir()
    {
        return FileUtils::path_join( PH_ROOT , 'templates');
    }

    public function getSmarty()
    {
        $smarty = new \Phifty\Bundle\Smarty;
        $smarty->template_dir = array( $this->getCodebaseDir() );
        $smarty->compile_dir  = FileUtils::path_join( PH_ROOT , 'cache' , 'templates' );
        return $smarty;
    }

    public function hasTemplate( $template )
    {
        return $this->smarty->templateExists( $template );
    }

    public function copyFile( $file, $sourceFile , $force ) 
    {
        $sourceFilePath = FileUtils::path_join( $this->getCodebaseDir() , $sourceFile );
        if( file_exists( $file ) && ! $force ) {
            echo "\tSkipping $file ...\n";
        } else {
            echo "\tCopying $file ...\n";
            copy( $sourceFilePath , $file );
        }
    }

    public function renderFile( $file, $template , $args = array() , $force = false )
    {
        $content = $this->render( $template , $args );
        if( file_exists( $file ) && ! $force ) {
            echo "\tSkipping $file.\n";
            return;
        }
        echo "\tPutting $file\n";
        file_put_contents( $file , $content );
    }

    public function render( $template , $args = array() )
    {
        foreach( $args as $key => $value )
            $this->smarty->assign( $key , $value );
        $code =  $this->smarty->fetch( $template );
        $this->smarty->clearAllAssign();
        return $code;
    }


}



