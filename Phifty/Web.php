<?php
namespace Phifty;

use Phifty\View;
use Phifty\WebUtils;
use Phifty\WebPath;
use Core\Application as CoreApplication;
use Phifty\Action\ActionRunner;
use Phifty\Asset\AssetLoader;

class Web
{

    private function buildCssTags( $paths )
    {
        $html = '';
        foreach( $paths as $path )
			$html .= WebUtils::cssTag( $path );
        return $html;
    }

    private function buildScriptTags( $paths )
    {
        $html = '';
        foreach( $paths as $path )
            $html .= WebUtils::jsTag($path);
        return $html;
    }

    /* expand glob file paths from $parentPath */
    public function globPaths( $parentPath, $fileList ) 
    {
        $webPaths = array();
        foreach( (array) $fileList as $path ) {
            $webPaths = array_merge( $webPaths , glob( $parentPath . DIR_SEP . $path ) );
        }
        return $webPaths;
    }

    /* baseUrl is mapping to parentPath,
     * we should remove parentPath and prepend baseUrl to the path of a file */
    private function buildUrls( $paths , $parentPath , $baseUrl )
    {
        $paths = array_map( function($file) use ( $parentPath , $baseUrl ) {
            return $baseUrl . '/' . FileUtils::remove_base( $file , $parentPath );
            } , $paths );
        return $paths;
    }

    private function getAppWebBaseUrl()
    {
		return WebPath::appBase();
    }

    private function getCoreWebBaseUrl()
    {
		return WebPath::coreBase();
    }

    public function includeMicroAppCss( $app , $webDir , $webBaseUrl )
    {
        $isDev = webapp()->isDev;
        /*
         * xxx:
         *   Currently css can not be minified because:
         *       1. related image paths (image not found)
         */
        if( 1 || $isDev ) {
            // relative file paths
            $fileList = $app->css();

            // turns into glob paths
            $filePaths = $this->globPaths( $webDir , $fileList );

            // convert paths to css tags
            return $this->buildCssTags( $this->buildUrls( $filePaths , $webDir , $webBaseUrl ) );
        }


        /* assume we've compressed the css */
        $id = $app->getId();
        $url = $webBaseUrl . '/css/minified.css';
        return $this->buildCssTags( array( $url ) );
    }

    /* include MicroApp Js */
    public function includeMicroAppJs( $app , $webDir , $webBaseUrl )
    {
        $isDev = webapp()->isDev;
        $fileList = $app->js();
        if( count($fileList) === 0 ) 
            return '';

        /* get absolute paths */
        $filePaths = $this->globPaths( $webDir , $fileList );

        /* convert to web urls */
        $webPaths = array();

        // disable minified js for now.
        if( 1 || $isDev ) {
            $webPaths = $this->buildUrls( $filePaths , $webDir , $webBaseUrl );
            return $this->buildScriptTags( $webPaths );
        }

# var_dump( $webDir ); 
# var_dump( $webBaseUrl );
# '/Users/c9s/git/Work/phifty/plugins/AdminUI/web'
# '/ph/plugins/AdminUI'

        $id = $app->getId();
        $url = $webBaseUrl . '/js/minified.js';
        return $this->buildScriptTags( array( $url ) );
    }


    /* ---------------------------------
     * methods export to template engine 
     * --------------------------------- */
    public function include_css( $path, $media = 'screen' , $charset = 'UTF-8' )
    {
		return WebUtils::cssTag( $path , $media , $charset );
    }

    public function include_js( $path )
    {
		return WebUtils::jsTag( $path );
    }

    /* 
     * to include front end js/css we have 3 steps 
     *
     * 1. glob all patterns , get a file list.
     * 2. remove base path and generate a url path list
     * 3. generate html
     *
     * should minify js when in production mode.
     *
     * */
    public function include_core()
    {
        return $this->include_core_css() 
            . $this->include_core_js();
    }

    public function include_core_css()
    {
        $core = CoreApplication::getInstance();
        return $this->includeMicroAppCss( $core , webapp()->getCoreWebDir() , WebPath::coreBase() );
    }

    public function include_core_js()
    {
        $core = CoreApplication::getInstance();
        return $this->includeMicroAppJs( $core , webapp()->getCoreWebDir() , WebPath::coreBase() );
    }

    public function include_plugins()
    {
        $html = '';
        $plugins = webapp()->plugin->getPlugins();
        if( $plugins ) {
            foreach( $plugins as $plugin ) {
                $html .= $this->includeMicroAppJs( $plugin , 
                    $plugin->locate() . DIRECTORY_SEPARATOR . 'web',
                    $plugin->getExportWebDir() 
                );
                $html .= $this->includeMicroAppCss( $plugin , 
                    $plugin->locate() . DIRECTORY_SEPARATOR . 'web',
                    $plugin->getExportWebDir() 
                );
            }
        }
        return $html;
    }

	public function include_assets()
	{
        return 
            AssetLoader::includeJsFiles() .
            AssetLoader::includeCssFiles();
	}

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

    public function langs()
    {
        return webapp()->locale->getLangList();
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
