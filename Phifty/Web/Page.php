<?php
namespace Phifty\Web;

class Page 
{
    public $template;

    /* default meta args */
    public $meta = array();

    /* default args */
    public $args = array();

    function __construct( $template = null )
    {
        if( $template )
            $this->template = $template;

        /* default meta args */
        $web = webapp()->web();
        $loader = $web->getLoader();
        $extendHeader =  $loader->render();

        $this->meta = array(
            "page" => array(
                "keywords" => "",
                "author" => "",
                "extend" => $extendHeader,
                "title"  => "",
            ),
            "action" => webapp()->actionRunner->getResults()
        );
        $this->prepare();
    }

    function prepare()
    {

    }

    /* meta args method for overriding */
    function getMeta()
    {
        return $this->meta;
    }

    /* normal args method for overriding */
    function getArgs()
    {
        return $this->args;
    }

    function __set( $name , $value )
    {
        $this->args[ $name ] = $value;
    }

    function __get( $name )
    {
        return $this->args[ $name ];
    }

    function getEngine()
    {
        return webapp()->getViewEngine('smarty');
    }

    function getTemplate()
    {
        return $this->template;
    }

    function setTemplate( $template )
    {
        $this->template = $template;
    }

    function apply( & $engine)
    {
        foreach( $this->getArgs() as $key => $value ) 
            $engine->assign( $key , $value );

        foreach( $this->getMeta() as $key => $value )
            $engine->assign( $key , $value );
    }

    function render() 
    {
        $engine = $this->getEngine();
        $this->apply( $engine );

        if( ! $this->template )
            throw new \Exception( "Template is not defined." );

        $engine->display( $this->template );
    }

    function __toString()
    {
        $engine = $this->getEngine();
        $this->apply( $engine );
        return $engine->fetch( $this->template );
    }
}

?>
