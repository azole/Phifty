<?php
namespace Phifty\Routing;


/**
 * Route hash structure:
 *
 * array( 
 *     'pattern' => string
 *     'default' =>  [ key => value ],
 *     'vars' => [ key => value ],
 *     'requirement' => [ 
 *          // require patterns
 *     ]
 * );
 */
class Route
{
    public $hash = array();

    function __construct( $hash )
    {
        $this->hash = $hash;
    }

    function __isset($name) {
        return isset($this->hash[$name]);
    }

    function __get($name) {
        if( isset($this->hash[ $name ] ) )
            return $this->hash[ $name ];
    }

    function get( $name )
    {
        if( isset($this->hash[ $name ] ) )
            return $this->hash[ $name ];
    }

    function getPrefix()
    {
        return $this->hash['prefix'];
    }

    function getRequirement()
    {
        return @$this->hash['requirement'];
    }

    function getDefault()
    {
        if( isset($this->hash['default'] ) ) 
            return $this->hash['default'];
        return array();
    }

    function getData()
    {
        return $this->hash;
    }

    function getVars()
    {
        return @$this->hash['vars']; // token => value.
    }

    function getPattern()
    {
        return @$this->hash['pattern'];
    }

    /* Get compiled pattern
     *
     */
    function getCompiledPattern()
    {
        return $this->hash['compiled'];
    }

    /* get route type
     *
     * @param $hash route hash
     *
     */
    static function getRouteType($hash)
    {
        if( isset( $hash['controller'] ) ) {
            return 'Controller';
        }
        elseif( isset( $hash['template'] ) ) {
            return 'Template';
        }
    }


    /* wrap the route hash to a route object
     *
     * @param $hash route hash
     *
     * */
    static function wrap($hash)
    {
        $type = static::getRouteType($hash);
        $class = '\Phifty\Routing\Route\\' . $type . 'Route';
        return new $class($hash);
    }

}

