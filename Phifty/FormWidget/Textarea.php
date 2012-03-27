<?php
namespace Phifty\FormWidget;
use Phifty\FormWidget;

class Textarea extends FormWidget
{
    public function template()
    {
        return <<<CODE

<textarea class="mceEditor" 
    name="{{ column.name }}" 
    {% for attr_name,attr_value in attrs %} 
    {{attr_name}}="{{attr_value}}" 
    {% endfor %}
    rows="10" 
    cols="90">{{ column.value }}</textarea>

CODE;
    }

}

