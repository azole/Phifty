<?php


namespace Phifty;

class ExceptionDisplay {

    public $template;
    public $exception;
    public $dev;

    function __construct($exception,$dev = false,$template = null) {
        $this->dev = $dev;
        $this->exception = $exception;
        $this->template = $template;
    }

    function renderTemplate() {
        $smarty = new Smarty3;
        $smarty->assign( "Exception" , $this->exception );
        $smarty->assign( "Dev" , $this->dev );
        $smarty->assign( "Lines" , $this->getLines() );
        $html = $smarty->fetch( $this->template ); 
        return $html;
    }

    function renderDefault() {
        $html = null;

        if( $this->dev ) {

            $lines = join( "", $this->getLines() );
            $html =<<<EN
<html>
    <pre>
        {$this->exception->getMessage()}
    </pre>
    <pre>
        {$lines}
    </pre>
</html>
EN;
        } 
        else
        {
            $html =<<<EN
<pre>
    Error
</pre>
EN;
        }
        return $html;
    }


    function getLines() {
        $e = $this->exception;

        $file = $e->getFile();
        $line = $e->getLine();
        $range = 10;
        $lines = file( $file );
        $startLine = ($line - $range) > 0 ? $line - $range : 0;
        return array_splice( $lines , $startLine , $range + $range );
    }

    function display() {
        if( $this->template )
            echo $this->renderTemplate();
        else
            echo $this->renderDefault();
    }
}


