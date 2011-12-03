<?php
namespace Phifty;
use Phifty\FileUtils;
use Phifty\Minifier\JSMin;
use Phifty\Minifier\CssMin;

class WebUtils 
{

    /* Minifty js files and return a minified content */
    static function minifyJsFiles( $files , $target )
    {
#          $cmd =  'cat ' .  join( ' ',$files ) . " | jsmin > $target";
#          echo $cmd;
#          system( $cmd );
#          return $target;


        $jsContent = FileUtils::concat_files( $files );
		$jsContent  = JSMin::minify( $jsContent );

        /* where do we put the minified js file ? */
        if( file_put_contents( $target , $jsContent , LOCK_EX ) === FALSE ) {
            throw new \Exception("minifyJsFiles $target failed.");
        }
        return $target;
    }

    static function cssImportCombineFile($cssfile)
    {
        $output = '';
        if( ! file_exists( $cssfile ) ) {
            echo "\tWARNING: $cssfile does not exist." , "\n";
            return '';
        }

        $lines = file( $cssfile );
        foreach( $lines as $line ) {
            if( preg_match( '/^@import\s*(["\'])(.*?)\1/' , $line , $regs ) ) {
                list($orig,$quote,$importFile) = $regs;
                $path = dirname( $cssfile) . '/' . $importFile;
                $output .= "/* ============ Import Start: css imported from $importFile */\n";
                $output .= self::cssImportCombineFile( $path ) . "\n";
                $output .= "/* ============ Import End: css imported from $importFile */\n";
                // var_dump( $importFile ); 
            } else {
                $output .= $line;
            }
        }
        return $output;
    }

	/*
	 * minifyCssFiles:  @files => @file
	*/
    static function minifyCssFiles( $files , $target )
    {
        $cssContent = '';
        foreach( $files as $file ) {
            $cssContent .= self::cssImportCombineFile( $file );
        }
#  		$cssContent = CssMin::minify( $cssContent, array(
#  			"remove-empty-blocks"           => false,
#  			"remove-empty-rulesets"         => false,
#  			"remove-last-semicolons"        => false,
#  			"convert-css3-properties"       => false,
#  
#  			"convert-font-weight-values"    => false, // new in v2.0.2
#  			"convert-named-color-values"    => false, // new in v2.0.2
#  			"convert-hsl-color-values"      => false, // new in v2.0.2
#  			"convert-rgb-color-values"      => false, // new in v2.0.2; was "convert-color-values" in v2.0.1
#  			"compress-color-values"         => false,
#  			"compress-unit-values"          => false,
#  			"emulate-css3-variables"        => false,
#  		) );
        if( file_put_contents( $target , $cssContent , LOCK_EX ) === FALSE ) {
            throw new \Exception("minifyCssFiles $target failed.");
        }
        return $target;
    }

	static function cssTag( $path , $media = 'screen' , $charset = 'UTF-8' )
	{
		$paths = (array) $path;
		$html = '';
		foreach( $paths as $path )
			$html .= '<link rel="stylesheet" type="text/css" href="' . $path . '" media="'. $media.'" charset="'. $charset .'"/>' . "\n";
		return $html;
	}

	static function jsTag( $path , $charset = 'UTF-8' )
	{
		$paths = (array) $path;
		$html = '';
		foreach( $paths as $path )
			$html .= '<script type="text/javascript" src="' . $path . '" charset="' . $charset . '"></script>' . "\n";
		return $html;
	}

}

