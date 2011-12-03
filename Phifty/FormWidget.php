<?php

namespace Phifty;
use Phifty\View\TwigLight;

abstract class FormWidget 
{
    public $attrs;
    public $column;
    public $defaultClass;
    public $uniqId;

    public function __construct( $column , $attrs = array() ) 
    {
        $this->column = $column;
        $this->uniqId = $this->genId();
        $this->defaultClass = 'phifty-widget-' . strtolower(get_class($this));
        $this->attrs = array_merge( array( 
            'class' => 'phifty-widget ' . $this->defaultClass,
            'id' => $this->uniqId,
        ) , $attrs );
        $this->setup();
    }

    public function setup() 
    {
    }

	public function setValue( $value )
	{
		$this->attrs[ 'value' ] = $value;
	}

    public function genId()
    {
        return uniqid( $this->column->name );
    }

    protected function getTemplate()
    {
        return $this->template();
    }

    public function addClass($class)
    {
        $this->attrs[ 'class' ] .= ' ' . $class;
    }

    /* user-defined template here */
    abstract function template();

    public function render( $attrs = array() )
    {
        $this->attrs = array_merge( $this->attrs , $attrs );
        $template_content = $this->getTemplate();
        $twig = TwigLight::getEngine();
        $template = $twig->loadTemplate( $template_content );
        return $template->render(array(
            'widget' => $this,
            'column' => $this->column,
            'attrs' => $this->attrs,
        ));
    }

}

