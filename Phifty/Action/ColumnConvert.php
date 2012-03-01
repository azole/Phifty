<?php
namespace Phifty\Action;

/**
 * Convert LazyORM column to Action column
 */
class ColumnConvert 
{

    static function toParam( $column , $record = null )
    {
		$name = $column->name;

        $param = new \Phifty\Action\Column( $name );

        foreach( $column->attributes as $k => $v ) {
            $param->$k = $v;
        }

        $param->name  = $name;
        $param->label = @$column->label;

		if( $record ) {
            $param->value = $record->{$name};
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



