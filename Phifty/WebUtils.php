<?php
namespace Phifty;
use Phifty\FileUtils;
use Phifty\Minifier\JSMin;
use Phifty\Minifier\CssMin;

class WebUtils 
{

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

