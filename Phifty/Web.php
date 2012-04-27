<?php
namespace Phifty;

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


    public function include_loaded_assets($name = null)
    {
        $kernel = kernel();
        $render = new IncludeRender;
        $writer = $kernel->asset->writer;
        if( $name )
            $kernel->name($name);
        $assets = $kernel->asset->loader->getAssets(); 
        $manifest = $writer->write($assets);
        return $render->render($manifest);
    }

    /**
     * @param string[] $assets asset names
     * @param string $name name
     *
     * {{ Web.include_assets ['jquery','jquery-ui'], 'page_name' }}
     */
    public function include_assets($assets, $name = null)
    {
        $kernel = kernel();
        $render = new IncludeRender;
        $writer = $kernel->asset->writer;
        if( $name )
            $kernel->name($name);

        $assets = array_map(function($n) use($kernel) {
                    return $kernel->asset->loader->load($n);
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
        $result = $runner->getResult( $resultName );
        $view = new \Phifty\View;
        $view->result = $result;
        return $view->render('Core/template/phifty/action_result_box.html');
    }

}
