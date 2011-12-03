<?php
namespace Phifty\FormWidget;
use Phifty\FormWidget;

class Checkbox extends FormWidget
{
    public function template()
    {
        return <<<CODE
    <input type="checkbox" 
            name="{{ column.name }}" 
            {% for attr_name,attr_value in attrs %} {{attr_name}}="{{attr_value}}" {% endfor %}
            {% if column.value %} checked {% endif %} value="1"/>
CODE;
    }

}

