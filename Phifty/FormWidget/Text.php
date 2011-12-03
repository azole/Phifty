<?php
namespace Phifty\FormWidget;
use Phifty\FormWidget;

class Text extends FormWidget
{
    public function template()
    {
        return <<<CODE
    <input type="text" 
            name="{{ column.name }}" 
            value="{{ column.value }}" 
    {% for attr_name,attr_value in attrs %} {{attr_name}}="{{attr_value}}" {% endfor %}/>
CODE;
    }

}

