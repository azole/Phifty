<?php
namespace Phifty;


/* 
 * A MicroController for controllers only contains templates 
 *
 * $con = new TemplateController( 'panel.html' );
 * $con->run();
 *
 * */
class TemplateController extends \Phifty\Controller
{

    /*
     * contains a template option hash:
     *
     *   engine
     *   template
     *   args
     *
     * */
    public $template;

    function __construct($args)
    {
        $this->template = $args;

        /* set template engine */
        if( isset($args['engine']) ) 
            $this->templateEngine = $args['engine'];
        parent::__construct();

    }

    /* implement template renderer for default controller */
    function run()
    {
        $this->render( $this->template['template'] , 
            $this->template['args'] 
        );
    }
}

?>
