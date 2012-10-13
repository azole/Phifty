<?php
namespace Phifty\Service;

class WKHtmlToPdf {

    public $bin;
    public $pageSize = 'A4';

    public function __construct($bin)
    {
        $this->bin = $bin;
    }

    public function convert($url,$target)
    {
        $cmds = array($this->bin);
        if( $this->pageSize ) {
            $cmds[] = '--page-size';
            $cmds[] = $this->pageSize;
        }
        $cmds[] = $url;
        $cmds[] = $target;
        $cmd = join(' ',$cmds);
        system($cmd);
        return $target;
    }
}

/**
 * WebKitHtmlToPdf
 *
 * Usage:
 *
 *  require 'main.php';
 *  kernel()->wkHtmlToPdf->convert('http://google.com','test.pdf');
 *  system('open test.pdf');
 */
class WKHtmlToPdfService
    implements ServiceInterface
{
    public function getId() { return 'wkhtmltopdf'; }

    public function register( $kernel , $options = array() )
    {
        $kernel->wkHtmlToPdf = function() use($kernel,$options) {
            return new WKHtmlToPdf($options['bin']);
        };
    }
}


