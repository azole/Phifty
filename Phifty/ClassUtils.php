<?php
namespace Phifty;
use ReflectionClass;

class ClassUtils 
{

    static function new_class( $class , $args = null )
    {
        $rc = new ReflectionClass( $class );
        if( $args )
            return $rc->newInstanceArgs( $args );
        return $rc->newInstance();
    }

}

