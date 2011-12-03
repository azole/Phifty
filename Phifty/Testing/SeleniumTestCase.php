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
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';
use PHPUnit_Extensions_SeleniumTestCase;
use Phifty\Testing\ShortHand;
use Phifty\Testing\ShortHandInterface;
 
abstract class SeleniumTestCase extends PHPUnit_Extensions_SeleniumTestCase
    implements ShortHandInterface
{

    function __call($method,$args)
    {
        $ret = ShortHand::call($this, $method, $args);
        if( $ret === false )
            return parent::__call($method,$args);
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


