<?php
namespace Phifty\FormWidget;
use Phifty\FormWidget;

class Checkbox extends FormWidget
{
    public function template()
    {
        return <<<CODE
    <input type="hidden"
        id="checkbox-{{ column.name }}"
        name="{{ column.name }}"
        value="{{ column.value }}"
    />
    <input type="checkbox" 
            {% for attr_name,attr_value in attrs %} {{attr_name}}="{{attr_value}}" {% endfor %}
            {% if column.value %} checked {% endif %}
            value="1" 
        onclick=" (function(btn) {
            var c = $(btn).attr('checked');
            $('#checkbox-{{ column.name }}').val( c ? 1 : 0 );
        })(this);"
    />
CODE;
    }

}

