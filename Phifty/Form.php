<?php

namespace Phifty;

/*

In my mind, Form is a kind of layout of data table.
So each element has its own way to render the data. (or in different ways everytime)

Form element classes should provide a way to gather data, but 
not to fix the way of rendering.

Form element classes should have its default way for rendering,
But we can also specify our templates for rendering.

    Form::radioset( ... data ... )->set_template( "TemplateClass" )->render();

    Form::radioset( ... data ... )->render_with( "template name" ); // should we use smarty ?

We should also provide a theme system.

    Form::select()->name("blah")->options( array( ... ) )->render();


XXX: we need an interface

 */

class Form {

    /*
    static function checkbox() 
    {
        return new FormCheckbox();
    }

    static function radio($values = null ) 
    {
        return new FormRadio( $values );
    }
     */

    static function select( $values = null ) 
    {
        return new \Phifty\Form\Select( $values );
    }

    /*
    static function input( $value = null ) 
    {
        return new \Phifty\Form\Input( $value );
    }
     */


    static function controll_buttons( $id , $model_name, $close = true ) 
    {
        $create_action = "Create" . $model_name;
        $update_action = "Update" . $model_name;
        ?>
        <? if( $id ) { ?>
            <input type="hidden" name="action" value="<?=$update_action?>"/>
            <input type="hidden" name="id" value="<?=$id?>"/>
            <input type="submit" value="Update"/>
        <? } else { ?>
            <input type="hidden" name="action" value="<?=$create_action?>"/>
            <input type="submit" value="Create"/>
        <? } ?>

        <? if( $close ) { ?>
            <input type="button" value="Close" onclick=" Region.of(this).fadeEmpty();"/>
        <? }
    }

}


class FormRadioSet 
{
    var $template_class;
    var $template_file;

    var $items = array();

    var $name;
    var $check_value;

    function __construct( $items = array() ) {
        $this->items = $items;
    }

    function name( $name ) {  $this->name = $name ; return $this; }

    function set_items( $items ) {
        $this->items = $items;
        return $this;
    }

    function add_item( $item , $value = null ) {
        if( is_array( $item ) ) {
            array_push( $this->items , $item );
        } else {
            if( is_string( $item ) && $value !== null )
                array_push( $this->items , array(
                    "label" => $item , 
                    "value" => $value ) );
        }
        return $this;
    }

    function render() {
        if( $this->template_class )
            return $this->render_with( $this->template_class );

        if( $this->template_file )
            return $this->render_with_file( $this->template_file );

        return $this->_default_render();
    }

    function set_template_file( $file ) {
        $this->template_file = $file;
        return $this;
    }

    function set_template_class( $class ) {
        $this->template_class = $class;
        return $this;
    }

    function render_with( $template_class ) {
        $t = new $template_class( $this );
        return $t->render();
    }

    function render_with_file( $file ) {
        $smt = SmartyFactory::create();
        $smt->assign( 'items' , $this->items );
        return $smt->fetch( $file );
    }

    function check( $val ) {
        $this->check_value = $val;
        return $this;
    }

    # XXX: use smarty for rendering ?
    function _default_render() {
        # default rendering
        $o = "";
        $idx = 0;
        foreach( $this->items as $item ) {
            $idx++;
            $name = $this->name ? $this->name : $item["name"];
            $id   =  @$item["id"] ? $item["id"] : "radio_" . $name . '_' . $idx;
            $value = $item['value'];

            $ra = new FormRadio( $name );
            $o .= '<div class="form-field-wrapper">';
                # $o .= '<div class="form-input">';

                $ra->id( $id )->val( $item['value'] );

                if( $this->check_value !== null && $ra->is( $this->check_value ) )
                    $ra->check();

                $o .= $ra->render();
                $o .= '<label for="'.$id.'">' . $item['label'] . "</label>";

                # $o .= '</div>';
            $o .= "</div>";
            $o .= "\n";
        }
        return $o;
    }
} 

?>
