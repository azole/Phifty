<?php
namespace Phifty\FormWidget;
use Phifty\FormWidget;

class Select extends FormWidget
{
    public $options = array();

    public function setup()
    {
        if( $this->column->validValues ) {
            foreach( $this->column->validValues as $value => $label ) {
                if( is_integer($value) )
                    $value = $label;
                $this->options[] = array( 'value' => $value , 'label' => $label );
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
<select name="{{ column.name }}" {% for attr_name,attr_value in attrs %} {{attr_name}}="{{attr_value}}" {% endfor %}>

        {% for option in widget.options %}
            {% if option.value == column.value %}
                <option value="{{ option.value }}" selected>{{ option.label }}</option>
            {% else %}
                <option value="{{ option.value }}">{{ option.label }}</option>
            {% endif %}
        {% endfor %}
</select>
CODE;
        return $content;
    }

}

