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

interface ShortHandInterface 
{
    function isString( $value , $msg = null );
    function isArray( $value , $msg = null );
    function printOk( $value , $msg = null );
    function countOk( $cnt, $array , $msg = null );
    function fileOk( $path , $msg = null );
    function dirOk( $dir , $msg = null );
}


