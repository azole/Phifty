<?php
namespace Phifty\Config;
use ArrayAccess;

class Accessor
    implements ArrayAccess
{

    public $config = array();

    function __construct($config = array() )
    {
        $this->config = $config;
    }

    
    public function offsetSet($name,$value)
    {
        $this->config[ $name ] = $value;
    }
    
    public function offsetExists($name)
    {
        return isset($this->config[ $name ]);
    }
    
    public function offsetGet($name)
    {
        if( isset($this->config) )
            return $this->config[ $name ];
    }
    
    public function offsetUnset($name)
    {
        return unset($this->config[$name]);
    }
    
    
}





