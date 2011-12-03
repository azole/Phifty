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

    public function template()
    {
        return <<<'CODE'
        <input type="text" name="{{ column.name }}" value="{{ column.value }}" 
            {% for attr_name,attr_value in attrs %} {{attr_name}}="{{attr_value}}" {% endfor %}/>
        <script type="text/javascript">
        $(function() {
            $('#{{ widget.uniqId }}').datepicker( { dateFormat: '{{widget.format}}' } );
        });
        </script>
CODE;

    }

}

