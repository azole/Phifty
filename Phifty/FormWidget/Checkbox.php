<?php
namespace Phifty\FormWidget;
use Phifty\FormWidget;

class Checkbox extends FormWidget
{
    public function template()
    {
        $id = time();
        return <<<CODE
    <input id="field-{{ column.name }}-$id" 
        type="hidden" 
        name="{{ column.name }}" 
        value="{% if column.value %}1{% else %}0{% endif %}"/>
    <input type="checkbox" 
        {% if column.value %} checked {% endif %} 
        onclick="
            var el = document.getElementById('field-{{ column.name }}-$id');
            el.value =  el.value == 1 ? 0 : 1;
        "/>
CODE;
    }

}

