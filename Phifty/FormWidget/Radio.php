<?php
namespace Phifty\FormWidget;
use Phifty\FormWidget;

class Radio extends FormWidget
{
    public $options = array();

    public function setup()
    {
        if( $this->column->validValues ) {
            foreach( $this->column->validValues as $value => $label ) {
                $this->options[] = array( 'value' => $value , 'label' => $label );
            }
        } elseif( $this->column->validPairs ) {
            foreach( $this->column->validPairs as $value => $label ) {
                $this->options[] = array( 'value' => $value , 'label' => $label );
            }
        }
    }

    public function template()
    {
        $content =<<<CODE
{% for option in widget.options %}
    {% if option.value == column.value %}
        <label>
            <input type="radio" name="{{ column.name }}" value="{{ option.value }}" checked/>
            {{ option.label }}</label>
    {% else %}
        <label>
            <input type="radio" name="{{ column.name }}" value="{{ option.value }}">
            {{ option.label }}</label>
    {% endif %}
{% endfor %}
CODE;
        return $content;
    }

}

