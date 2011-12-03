<?php
namespace Phifty\Testing;
use PHPUnit_Framework_TestCase;
use Exception;
use Phifty\Testing\ShortHand;
use Phifty\Testing\ShortHandInterface;

class TestCase extends \PHPUnit_Framework_TestCase
    implements ShortHandInterface
{
    function __call( $method , $args )
    {
        $ret = ShortHand::call( $this, $method, $args );
        if( $ret === false )
            return parent::__call( $method, $args );
    }

    function ok( $value , $msg = null ) 
    {
        $this->assertTrue( $value ? true : false , $msg );
    }

    function notOk( $value, $msg = null )
    {
        $this->assertTrue( $value ? false : true , $msg );
    }

    function isString( $value , $msg = null )
    {
        $this->assertTrue( is_string( $value ) , $msg );
    }

    function isArray( $value , $msg = null )
    {
        $this->assertTrue( is_array( $value ) , $msg );
    }

    /* ok and print value */
    function printOk( $value , $msg = null )
    {
        $this->assertNotEmpty( $value , $msg );
        print_r( $value );
        echo "\n";
    }

    function countOk( $cnt, $array , $msg = null ) 
    {
        $this->ok( count($array) == $cnt , $msg );
    }


    function fileOk( $path , $msg = null )
    {
        $this->ok( file_exists( $path ) , $msg );
    }

    function dirOk( $dir , $msg = null )
    {
        $this->ok( is_dir( $dir ) , $msg );
    }

}

