<?php
/*
 * This file is part of the {{ }} package.
 *
 * (c) Yo-An Lin <cornelius.howl@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
namespace Phifty\Testing;


/* inject shorthand functions like: ok, is ... etc */
class ShortHand
{

    static function call($self,$method,$args)
    {
        /* we can use debug_backtrace to get the caller object */
        // $ret = debug_backtrace();
        // var_dump( $ret[0][ 'class' ] ); 
        $mappings = array( 
            "is"       => "assertEquals",
            "isa"      => "assertInstanceOf",
            "same"     => "assertSame",
            "contains" => "assertContains",
            "isNull"   => "assertNull",
            "isEmpty"  => "assertEmpty",
            "notEmpty" => "assertNotEmpty",

            "like"     => "assertRegExp",

            // "ok"       => "assertNotEmpty",
            // "notOk"    => "assertEmpty",

            "endsWith"   => "assertStringEndsWith",
            "startsWith" => "assertStringStartsWith",
        );

        $origMethod = @$mappings[ $method ];
        if( $origMethod ) {
            if( ! method_exists( $self, $origMethod ) )
                throw new Exception( "$origMethod doesnt exist." );
            call_user_func_array( array( $self, $origMethod ) , $args );
            return true;
        }
        return false;
    }

}

