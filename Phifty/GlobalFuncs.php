<?php

namespace {

function array_insert( & $array , $pos = 0 , $elements )
{
    array_splice( $array, $pos , 0 , (array) $elements );
}

function print_backtrace($stacks)
{
    $cnt = 1;
    foreach( $stacks as $stack )
    {
        $cnt++;
        printf( '%s> %s:%s  ' , str_repeat('=',$cnt*2) ,  $stack['file'] , $stack['line'] );

        if( isset($stack['class']) )
            echo $stack['class'] . $stack['type'];

        if( isset($stack['function']) )
            echo $stack['function'];


        if( isset( $stack['object'] ) ) {
            echo "\n";
            var_dump( $stack['object'] );
        }

        if( isset( $stack['args'] ) ) {
            echo "\n";
            var_dump( $stack['args'] );
        }

        echo "\n";
    }
}

function croak($message , $return = false)
{
    $stacks = debug_backtrace();

    // remove current function stack
    array_shift( $stacks );
    print_backtrace( $stacks );
    if( $return )
        return $stacks;
}

function croak_log($message)
{
    $stacks = debug_backtrace();
    array_shift( $stacks );
    $return = $message . "\n\n" . print_r(  $stacks , true );
    error_log( $return , 1 , join("\n",array('cornelius.howl@gmail.com','Subject: PHP Error')) );
}

/* 
    croak_log(); 
 * */
}

?>
