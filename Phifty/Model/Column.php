<?php
namespace Phifty\Model;

class Column extends \LazyRecord\Column
{
    public $widgetClass;
    public $widgetAttrs = array(); /* TODO: */
    public function renderAs( $type ) {
        $this->widgetClass = $widgetClass = 
            ( $type[0] == '\\' ) ? $type : '\Phifty\FormWidget\\' . $type;
        return $this;
    }
}

