<?php


/*
    $input = new FormInput( 'action' , 'CreateProduct' );
    $input->render();

 */

namespace Phifty\Form;

use Phifty\Form\Widget;

class Input extends Widget 
{

    var $tagname = "input";
    var $closed = true;

    var $name;
    var $value;

    function __construct( $name , $value = null ) 
    {
        $this->attrs['name'] = $name;
        $this->value = $value;
    }

    function type( $type ) {
        $this->attrs[ 'type' ] = $type;
        return $this;
    }

    function val( $value ) { 
        $this->attrs[ 'value' ] = $value;
        return $this;
    }

}



?>
