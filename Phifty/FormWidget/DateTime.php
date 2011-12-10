<?php
namespace Phifty\FormWidget;
use Phifty\FormWidget;

class DateTime extends FormWidget
{
    public $format = 'yy-mm-dd';

    public function setFormat($format)
    {
        $this->format = $format;
    }

	public function getValue()
	{
		$value = $this->column->value;
		if( $value == null || substr($value,0,4) === '0000' )
			return '';
		return $value;
	}

    public function template()
    {
        return <<<'CODE'
        <input type="text" name="{{ column.name }}" value="{{ widget.value }}" 
            {% for attr_name,attr_value in attrs %} {{attr_name}}="{{attr_value}}" {% endfor %}/>
        <script type="text/javascript">
        $(function() {
            $('#{{ widget.uniqId }}').datepicker( { dateFormat: '{{widget.format}}' } );
        });
        </script>
CODE;

    }

}

