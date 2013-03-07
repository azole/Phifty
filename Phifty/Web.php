<?php
namespace Phifty;
use Exception;
use Phifty\View;
use Phifty\WebUtils;
use ActionKit\ActionRunner;

use AssetToolkit\AssetRender;
use AssetToolkit\AssetConfig;

class Web
{

    public function render_all_results()
    {
        $runner = kernel()->action;
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
        $assets = $kernel->asset->loader->all();
        return $kernel->asset->render->renderAssets($assets);
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
        $assetObjs = $kernel->asset->loader->loadAssets($assets);
        return $kernel->asset->render->renderAssets($assetObjs,$name);
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
