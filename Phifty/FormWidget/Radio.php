<?php
namespace Phifty\FormWidget;
use Phifty\FormWidget;

class Radio extends FormWidget
{
    public $options = array();

    public function setup()
    {
        if( $this->column->validValues ) {
            foreach( $this->column->validValues as $val ) {
                $this->options[] = array( 'value' => $val , 'label' => $val );
            }
        } elseif( $this->column->validPairs ) {
            foreach( $this->column->validPairs as $value => $label ) {
                $this->options[] = array( 'value' => $value , 'label' => $label );
            }
        } elseif( $this->column->refer ) {
            /* we have refer */

            // TODO: performance
            $class = $this->column->refer;
            $record = new $class;
            $collection = $record->asCollection();
            $collection->fetch();  // TODO: custom selections
            foreach( $collection as $id => $item ) {
                $this->options[] = array(
                        'value' => $item->id, 
                        'label' => $item->dataLabel() );
            }
        }
    }

    public function template()
    {
        $content =<<<CODE
{% for option in widget.options %}
    {% if option.value == column.value %}
        <label>
            <input name="{{ column.name }}" value="{{ option.value }}" checked/>
            {{ option.label }}</label>
    {% else %}
        <label>
            <input name="{{ column.name }}" value="{{ option.value }}">
            {{ option.label }}</label>
    {% endif %}
{% endfor %}
CODE;
        return $content;
    }

}

