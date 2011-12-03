<?php

namespace Phifty;

class StringUtils 
{
    static function removeNs( $class )
    {
        $pos = strrpos( $class, '\\' );
        if( $pos !== false )
            return substr( $class , $pos + 1 );
        return $class;
    }
}


