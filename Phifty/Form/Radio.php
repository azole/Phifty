<?php


namespace Phifty\Form;

use Phifty\Form\Input;

class Radio extends Input 
{
    var $name;
    var $type = 'radio';
    var $checked = false;

    function is( $val ) {
        return @$this->attrs['value'] == $val;
    }

    function attr_string() {
        $str = parent::attr_string();
        if( $this->checked )
            $str .= ' checked ';
        return $str;
    }


    function check() { 
        $this->checked = true;
        return $this;
    }

}

?>
