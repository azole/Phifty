<?php
namespace Phifty\Form;

use Phifty\Form\Widget;

/*

    $widget = new FormSelect( array(  ) );
    $widget->render();
    $widget->render_with();

*/

class Select extends \Phifty\Form\Widget {

    var $tagname = "select";
    var $options = array();
    var $selected;

    function __construct( $options , $selected = null ) {
        $this->options = $options;
        $this->selected = $selected;
    }

    function options( $list ) {
        if( is_string( $list ) ) {
            $this->options = explode( ' ', $list );
        }
        else { 
            $this->options = $list;
        }
        return $this;
    }

    function add_option( $name , $value ) { 
        $this->options[] = array( $name , $value );
        return $this;
    }

    function select( $value ) { 
        $this->selected = $value;
        return $this;
    }

    function render_options() {
        $str = "";
        foreach( $this->options as $item ) {
            $selected = " ";

            if( is_array($item) ) {
                if( $this->selected == $item[0] )
                    $selected = " selected";

                $str .= "\t" . '<option value="' . $item[0] . '"' . $selected . '>' . $item[1] . '</option>' . "\n";
            } else {
                if( $this->selected == $item )
                    $selected = " selected";

                $str .= "\t" . '<option value="' . $item . '"'. $selected .'>' . $item . '</option>' . "\n";
            }

        }
        return $str;
    }

    function get_html() {  
        return $this->render_options();
    }

}

?>
