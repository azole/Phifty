<?php
namespace Phifty;

class Event
{
    static $beforeEvents = array();
    static $events       = array();
    static $afterEvents  = array();

    static function add( $name , $cb ) 
    {
        if( ! isset( self::$events[ $name ] ) ) {
            if( $cb ) 
                self::$events[ $name ] = array( $cb );
            else
                self::$events[ $name ] = array();
        }
        else {
            self::$events[ $name ][] = $cb;
        }
    }

    static function clear( $name )
    {
        unset( self::$beforeEvents[ $name ] );
        unset( self::$events[ $name ] );
        unset( self::$afterEvents[ $name ] );
    }

    static function clearAll()
    {
        self::$beforeEvents = array();
        self::$events = array();
        self::$afterEvents = array();
    }

    /*
        Event::run( 'system.post_controller' );
    */
    static function run()
    {
        $args = func_get_args();
        $name = array_shift($args);

        if( isset( self::$beforeEvents[$name] ) ) 
            foreach( self::$beforeEvents[$name] as $cb ) {
                call_user_func_array( $cb , $args );
            }

        if( isset( self::$events[ $name ] ) ) 
            foreach( self::$events[ $name ] as $cb ) {
                call_user_func_array( $cb , $args );
            }

        if( isset( self::$afterEvents[ $name ] ) )
            foreach( self::$afterEvents[ $name ] as $cb ) {
                call_user_func_array( $cb , $args );
            }

    }


}




?>
