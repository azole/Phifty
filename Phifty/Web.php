<?php
namespace Phifty;
use Exception;
use Phifty\View;
use Phifty\WebUtils;
use ActionKit\ActionRunner;
use AssetKit\IncludeRender;

class Web
{

    public function render_all_results()
    {
        $runner = ActionRunner::getInstance();
        $results = $runner->getResults();
        $html = '';
        foreach( $results as $key => $value ) {
            $html .= $this->render_result( $key );
        }
        return $html;
    }


    /**
     * @param string $name
     */
    public function include_loaded_assets($name = null)
    {
        $kernel = kernel();
        $render = new IncludeRender;
        $writer = $kernel->asset->writer;
        if( $name )
            $writer->name($name);
        $assets = $kernel->asset->loader->getAssets(); 
        $manifest = $writer->write($assets);
        return $render->render($manifest);
    }

    /**
     * @param string[] $assets asset names
     * @param string $name name
     *
     * {{ Web.include_assets(['jquery','jquery-ui'], 'page_name')|raw}}
     */
    public function include_assets($assets, $name = null)
    {
        $kernel = kernel();
        $render = new IncludeRender;
        $writer = $kernel->asset->writer;
        if( $name )
            $writer->name($name);
        $assets = array_map(function($n) use($kernel) {
                    $a = $kernel->asset->loader->load($n);
                    if( ! $a )
                        throw new Exception("Asset $n can not be loaded, asset not found.");
                    return $a;
                },$assets);
        $manifest = $writer->write($assets);
        return $render->render($manifest);
    }

    public function langs()
    {
        return kernel()->locale->getLangList();
    }

    public function get_result( $resultName )
    {
        $runner = ActionRunner::getInstance();
        return $runner->getResult( $resultName );
    }

    public function render_result( $resultName )
    {
        $runner = ActionRunner::getInstance();
        if( $result = $runner->getResult( $resultName ) ) {
            $view = new \Phifty\View;
            $view->result = $result;
            return $view->render('Core/template/phifty/action_result_box.html');
        }
    }

}
