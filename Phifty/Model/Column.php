<?php
namespace Phifty\Model;
use LazyRecord\Schema\SchemaDeclare\Column as DeclareColumn;

class Column extends DeclareColumn
{
    public $widgetClass;
    public $widgetAttrs = array(); /* TODO: */

    public function renderAs( $type ) {
        $this->widgetClass = $widgetClass = 
            ( $type[0] == '\\' ) ? $type : '\Phifty\FormWidget\\' . $type;
        return $this;
    }
}

