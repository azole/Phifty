<?php
namespace Phifty\View;
use Twig_Environment;
use Twig_Extensions_Extension_Debug;
use Twig_Loader_String;

class TwigLight 
{

    static function getEngine()
    {
        static $engine;
        if( $engine )
            return $engine;
        $loader = new \Twig_Loader_String();
        $twig = new \Twig_Environment($loader,array(
            'debug' => true,
            # cache doesnt work here.
            # 'auto_reload' => true,
        ));
        $debug = new \Twig_Extensions_Extension_Debug;
        $twig->addExtension( $debug );
        return $engine = $twig;
    }
}


