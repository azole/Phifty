<?php
namespace Phifty\Action;

class ColumnConvert 
{

    static function toParam( \LazyRecord\Column $column , $record = null )
    {

		$name = $column->name;
        $param = new \Phifty\Action\Column( $name );

        $cloneAttrs = array(
            'validValues',
            'validPairs',
            'immutable',
            'refer',
            'defaultValue',
			'unique',
            'required',
            'label',
            'widgetClass',
        );
        foreach( $cloneAttrs as $attr ) {
            $param->$attr = $column->$attr;
        }

		if( $record ) {
			$recordValue = $record->value( $name );
            $param->value( $recordValue );
		}

        /* convert column type to param type
         *
         * set default render widget
         * */
        if( $param->validValues || $param->validPairs )
            $param->renderAs( 'Select' );

        if( ! $param->widgetClass )
            $param->renderAs( 'Text' );

        return $param;
    }


}



