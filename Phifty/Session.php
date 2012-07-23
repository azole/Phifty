<?php
namespace Phifty;

class Session 
{
	public $sessionPrefix;

	function __construct( $sessionPrefix = '' )
	{
		$this->sessionPrefix = $sessionPrefix;
	}

	function __set( $name , $value ) 
	{
		$this->set( $name , $value );
	}

	function __get( $name )
	{
		return $this->get( $name );
	}

    function __isset( $name )
    {
        return isset( $_SESSION[ $this->sessionPrefix . $name ] );
    }

	function set($name,$value)
	{
        $key = $this->sessionPrefix . $name;
		$_SESSION[ $key ] = $value;
	}

	function get($name)
	{
        $key = $this->sessionPrefix . $name;
		return @$_SESSION[ $key ];
	}

	function remove($name)
	{
        $key = $this->sessionPrefix . $name;
		unset( $_SESSION[ $key ] );
	}

	function getAll()
	{
		$args = array();
		foreach( $_SESSION as $key => $value )
			if( strpos( $key , $this->sessionPrefix ) === 0 )
				$args[ $key ] = $value;
		return $args;
	}

	function has($name)
	{
		return isset( $_SESSION[ $this->sessionPrefix . $name ] );
	}

	function setArgs( $args )
	{
		foreach( $args as $key => $value ) {
			$_SESSION[ $this->sessionPrefix . $key ] = $value;
		}
	}

	function doExpire( $minutes )
	{
		session_cache_expire( $minutes );
	}

	function getExpire()
	{
		return session_cache_expire();
	}

	function getId()
	{
		return session_id();
	}

	function decode( $data )
	{
		return session_decode( $data );
	}

	function encode()
	{
		return session_encode();
	}

    function clear()
    {
        session_unset();

        /* force session var */
        $_SESSION = array();
    }

	function destroy()
	{
		session_destroy();
        $this->clear();
	}
}

