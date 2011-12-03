<?php

namespace Phifty\Form;

use Phifty\Form\Input;

class Checkbox extends Input {
    var $type = 'checkbox';
    var $checked = false;
    function check( ) {
        $this->checked = true;
        return $this;
    }
}

