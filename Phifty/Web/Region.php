<?php
namespace Phifty\Web;
use Phifty\View\TemplateView;

class Region extends TemplateView
{
    public $path;

    public $arguments = array();

    public function __construct($path, $arguments = array()) 
    {
        $this->path = $path;
        $this->arguments = $arguments;
    }


    public function getRegionId()
    {
        return preg_replace('#\W+#', '_', $this->path) . '-' . md5(microtime());
    }

    public function getTemplate()
    {
        return <<<TEMPL
<div id="{{View.getRegionId()}}" class="__region">

</div>
<script type="text/javascript">
$(document.body).ready(function() {
    $('#{{View.getRegionId()}}').asRegion().load( '{{View.path|raw}}' , {{ View.arguments|json_encode|raw }} );
});
</script>
TEMPL;

    }

    public function render() 
    {
        return $this->renderTemplateString($this->getTemplate(), $this->mergeTemplateArguments());
    }


    public function __toString() 
    {
        return $this->render();
    }



}




