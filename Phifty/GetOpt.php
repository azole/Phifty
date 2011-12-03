<?php
namespace Phifty;

/*

$opt = new GetOpt;
$opt->longopts(array(
    "M|model",
));

*/

class GetOptResult 
{
    public $options = array();

    function __construct( $options ) 
    {
        $this->options = $options;
    }

    function __set( $key , $value ) 
    {
        $this->options[ $key ] = $value;
    }

    function __get( $key ) 
    {
        $key = str_replace( '_' , '-' , $key );
        return @$this->options[ $key ];
    }

    function getKeys() 
    {
        return array_keys($this->options);
    }

    function hasOne()
    {
        return count(array_keys($this->options)) > 0 ? true : false;

    }

    function getData()
    {
        return $this->options;
    }

    function __toString() {
        return json_encode( $this->options );
    }




}

class GetOpt 
{

    public $argv;
    public $longOpts = array();
    public $shortOpts = "";

    public $relation = array();

    /* xxx: not used */
    public $descs = array();

    public function __construct( $short = null ,  $long = null )
    {
        if( $short ) 
            $this->shortOpts = $short;
        if( $long )
            $this->longOpts  = $long;
    }


    public function setLong( $opts ) 
    {
        $this->longOpts = $opts;
    }

    public function setShort( $opts )
    {
        $this->shortOpts = $opts;
    }

    public function addLong( $opt ) 
    {
        $this->longOpts[] = $opt;
    }

    public function addShort( $opt )
    {
        $this->shortOpts .= $opt;
    }


    function _getValue( $index )
    {
        $current = $this->argv[$index];

        // if current option argv contains value
        if( preg_match( '/[a-zA-Z0-9-]+=(.*)$/',$current,$regs) ) {
            list($orig,$value) = $regs;
            return $value;
        }
        /* try to get the value from next position */
        else if( count($this->argv) > $index + 1 ) {
            $value = @$this->argv[$index + 1];

            // is an option
            if( strstr($value, '-') === 0 )  {
                return null;
            }

            return $value;
        } 
    }


    function hasOption( $option , $multiple = false )
    {
        if( $multiple ) {
            $values = array();
            $pos = $this->getOptionPos( $option , 0 );

            while( $pos !== false ) {
                $values[] = 1;
                $pos = $this->getOptionPos( $option , $pos + 1 );
            }
            return $values;
        } else {
            $pos = $this->getOptionPos( $option );

            /* we found option */
            if( $pos !== false ) {
                return 1;
            }
        }
        return null;
    }

    function getOptionValue($option,  $multiple = false)
    {
        if( $multiple ) {
            $values = array();
            $pos = $this->getOptionPos( $option , 0 );

            while( $pos !== false ) {
                $values[] = $this->_getValue( $pos );
                $pos = $this->getOptionPos( $option , $pos + 1 );
            }
            return $values;
        } else {
            $pos = $this->getOptionPos( $option );

            /* we found option */
            if( $pos !== false ) {
                return $this->_getValue( $pos );
            }
        }
        return null;
    }

    function getOptionPos($option, $start = 0)
    {
        for( $i = $start ; $i < count($this->argv); $i++ ) {
            $v = $this->argv[ $i ];
            if( $v == $option 
                || preg_match( "/^$option\$/" , $v ) 
                || preg_match( "/^$option=/" , $v ) 
            ) return $i;
        }
        return false;
    }

    public function parse( $argv )
    {
        $this->argv = $argv;
        $result = array();

        /* prebuild short options from long option,
         * extended format support by ourself. */
        foreach( $this->longOpts as $option ) 
        {
            if( preg_match( '/^(\w)
                            \|
                            ([a-zA-Z0-9-]+)
                            (:*)(\+?)$/x' , $option , $regs ) ) 
            {
                list( $orig, $shortName, $longName , $type , $multiple ) = $regs;

                $this->relation[ $shortName ] = $longName;

                /* append to shortopts */
                $this->shortOpts .= $shortName . $type . $multiple;
            }
        }


        /* parse short options */
        if( preg_match_all( '/(\w)(:*)(\+?)/i', $this->shortOpts , $matches ) ) 
        {

            // + plus sign is for multiple values
            for( $i = 0 ; $i < count($matches[1]) ; $i++ ) {
                $char = @$matches[ 1 ][ $i ];
                $type = @$matches[ 2 ][ $i ];
                $multiple = @$matches[ 3 ][ $i ] ? true : false;
                
                if(  strlen($type) == 0 ) {
                    // no-value
                    $values = $this->hasOption( "-$char",  $multiple );
                    if( $values === null )
                        continue;

                    if( $values ) {
                        $result[ $char ] = $values;

                        if( @$this->relation[ $char ] ) {
                            $long = $this->relation[ $char ];
                            $result[ $long ] = $values;
                        }
                    }
                }
                elseif( strlen($type) == 1 ) {
                    $has = $this->hasOption( "-$char",  $multiple );
                    if( $has === null )
                        continue;


                    // required value
                    $values = $this->getOptionValue( "-$char" , $multiple );
                    if( ! $values ) {
                        die( "Option -$char required a value." );
                    }
                    $result[ $char ] = $values;

                    if( @$this->relation[ $char ] ) {
                        $long = $this->relation[ $char ];
                        $result[ $long ] = $values;
                    }
                }
                elseif( strlen($type) == 2 ) {
                    $has = $this->hasOption( "-$char",  $multiple );
                    if( $has === null )
                        continue;


                    // optional value
                    $values = $this->getOptionValue( "-$char" , $multiple );
                    if( $values ) {
                        $result[ $char ] = $values;

                        if( @$this->relation[ $char ] ) {
                            $long = $this->relation[ $char ];
                            $result[ $long ] = $values;
                        }
                    }

                }
                else {
                    die("unknown attribute type");
                }
            }
        }

        /*
        parse long option names
         */
        foreach( $this->longOpts as $option ) 
        {
            if( preg_match( '/^(?:\w\|)?([a-zA-Z0-9-]+)(:*)(\+?)$/' , $option , $reg ) ) {

                list( $orig, $name , $type , $multiple ) = $reg;

                switch( strlen($type) )
                {
                /* no-value */
                case 0:
                    $values = $this->hasOption( "--$name",  $multiple );



                    if( $values )
                        $result[ $name ] = $values;
                    break;
                /* required-value */
                case 1:
                    $has = $this->hasOption( "--$name",  $multiple );
                    if( $has === null )
                        continue;


                    $values = $this->getOptionValue( "--$name" , $multiple );
                    if( ! $values )
                        die( "Option --$name required a value." );
                    $result[ $name ] = $values;
                    break;
                case 2:
                    $has = $this->hasOption( "--$name",  $multiple );
                    if( $has === null )
                        continue;


                    // optional value
                    $values = $this->getOptionValue( "--$name" , $multiple );
                    if( $values )
                        $result[ $name ] = $values;
                    break;
                default:
                    break;
                }

                /*
                $pos = $this->getOptionPos( '--' . $name );
                if( $pos !== false ) {

                    if( strlen($type) == 1 ) {
                        $result[ $name ] = $value = $this->getOptionValue( '--' . $name );

                        // required value
                        if( $value === null )
                            die( "Option --$name required a value." );


                    } elseif( strlen($type) == 2 ) {
                        // optional value
                        if( $pos !== false ) 
                            $result[ $name ] = $value = $this->getOptionValue( '--' . $name );
                    } else {
                        // no value
                        if( $pos !== false )
                            $result[ $name ] = 1;
                    }

                }
                 */
            }

        }

        return new GetOptResult( $result );
    }


}




