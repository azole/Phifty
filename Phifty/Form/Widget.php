<?php

namespace Phifty\Form;

class Widget
{
    var $tagname;
    var $closed = false;
    var $type;
    var $attrs = array();

    function name( $name ) 
    {
        $this->attrs["name"] = $name; 
        return $this;
    }

    function add_class( $class ) 
    {
        $this->attrs["class"] .=  " " . $class; 
        return $this;
    }

    function id( $id ) 
    {
        $this->attrs[ "id" ] = $id; return $this; 
    }

    function attr_string() 
    {
        $str = " ";
        $str .= sprintf( 'type="%s" ' , $this->type );
        foreach( $this->attrs as $key => $value )
            $str .= $key . '="' . $value . '" ';

        return $str;
    }

    function render_pair( $html = "" )
    {
        $output = "<" . $this->tagname . $this->attr_string() 
                . ">\n"
                . $html
                . "</" . $this->tagname . ">\n";
        return $output;
    }

    function render_closed() 
    {
        return "<" . $this->tagname . $this->attr_string() . "/>";
    }

    function get_html() 
    {
        return $this->html;
    }

    function render( $html = null )
    {
        if( $html )
            $this->html = $html;
        return $this->closed ? $this->render_closed() : $this->render_pair( $this->get_html() );
    }
}



?>
