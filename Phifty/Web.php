<?php
namespace Phifty;

use Phifty\View;
use Phifty\WidgetLoader;
use Phifty\WebUtils;
use Phifty\WebPath;
use Core\Application as CoreApplication;
use Phifty\Action\ActionRunner;

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


    private function getjQueryVersion()
    {
        return webapp()->config('web.jquery');
    }


    /* web path */
    private function getjQueryPath()
    {
        $version = $this->getjQueryVersion();
        return 'js/jquery/jquery-' . $version . '.js';
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
         * FIXME:
         *   Currently css can not be minified because:
         *       1. related image paths (image not found)
         */
        if( $isDev ) {
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
        if( count($fileList) == 0 ) 
            return '';

        /* XXX: prepend jQuery */
#          $jqueryPath = $this->getjQueryPath();
#          array_unshift( $fileList , $jqueryPath );

        /* get absolute paths */
        $filePaths = $this->globPaths( $webDir , $fileList );

        /* convert to web urls */
        $webPaths = array();
        if( $isDev ) {
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
        $core = CoreApplication::one();
        return $this->includeMicroAppCss( $core , webapp()->getCoreWebDir() , WebPath::coreBase() );
    }

    public function include_core_js()
    {
        $core = CoreApplication::one();
        return $this->includeMicroAppJs( $core , webapp()->getCoreWebDir() , WebPath::coreBase() );
    }

    public function include_plugins()
    {
        $html = '';
        $plugins = \Phifty\PluginPool::one()->getPlugins();
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

	public function include_widgets()
	{
        return 
            WidgetLoader::includeJsFiles() .
            WidgetLoader::includeCssFiles();
	}

    public function render_all_results()
    {
        $runner = ActionRunner::one();
        $results = $runner->getResults();
        $html = '';
        foreach( $results as $key => $value ) {
            $html .= $this->render_result( $key );
        }
        return $html;
    }

    public function langs()
    {
        return webapp()->l10n->getLangList();
    }


    public function get_result( $resultName )
    {
        $runner = ActionRunner::one();
        return $runner->getResult( $resultName );
    }

    public function render_result( $resultName )
    {
        $runner = ActionRunner::one();
        $result = $runner->getResult( $resultName );
        $view = new \Phifty\View;
        $view->result = $result;
        return $view->render('Core/template/phifty/action_result_box.html');
    }

}


/*
class Web {

	public $loader;
	public $kernel;

	function __construct( $kernel ) {
		$this->kernel = $kernel;

		// XXX: get config config here
		$loader = new Loader(array( 
			'root_dir' => PH_APP_ROOT,
			'web_dir'  => "web",
			'cache_dir' => PH_APP_ROOT . '/web/cache',

			# "base_url" => "/git/lart/..../web",
			'minify'  => false
		));

		$loader->import( array(
            'name' => 'phifty.core',
			'minify' => true,
			'mapping' => array( PH_APP_ROOT . '/phifty/web','ph'), // replace phifty/web to ph (or base path from config)
			'js' => array( 
				PH_APP_ROOT . '/phifty/web/js/jquery-1.5.2.min.js', // path based on PH_APP_ROOT
				PH_APP_ROOT . '/phifty/web/js/jquery.history.js',
				PH_APP_ROOT . '/phifty/web/js/jquery.oembed.js',
				PH_APP_ROOT . '/phifty/web/js/jquery.scrollTo-min.js',
				PH_APP_ROOT . '/phifty/web/js/webtoolkit.aim.js',
			),
			'css' => array( 
				PH_APP_ROOT . '/phifty/web/css/custom-jgrowl.css',
				PH_APP_ROOT . '/phifty/web/css/pager.css',
			)
		));

        $loader->import( array(
            'name' => 'blueprint',
            'minify' => false,
			'mapping' => array( PH_APP_ROOT . '/phifty/web','ph'),
            'css' => PH_APP_ROOT . '/phifty/web/css/blueprint/compressed/screen.css'
        ));

        $loader->import( array( 
			'minify' => false,
			'mapping' => array( PH_APP_ROOT . '/phifty/web','ph'), // replace phifty/web to ph (or base path from config)
			'js' => array( 
				PH_APP_ROOT . '/phifty/web/js/minilocale.js',
				PH_APP_ROOT . '/phifty/web/js/region.js',
				PH_APP_ROOT . '/phifty/web/js/action.js',
            ),
            'css' => array(
				PH_APP_ROOT . '/phifty/web/css/jquery-region/themes/basic.css',
				PH_APP_ROOT . '/phifty/web/css/action.css',
				PH_APP_ROOT . '/phifty/web/css/main.css'
            )
        ));

        $loader->import( array( 
			'minify' => false,
            'name' => 'jquery-ev',
			'mapping' => array( PH_APP_ROOT . '/phifty/web','ph'), // replace phifty/web to ph (or base path from config)
			'js' => array( PH_APP_ROOT . '/phifty/web/js/jquery.ev.js' )
        ));

		// jQuery UI
		$loader->import( array( 
			'minify'  => false,
			'mapping' => array( PH_APP_ROOT . '/phifty/web' , 'ph'),
			'js'      => PH_APP_ROOT . '/phifty/web/js/jqueryui/js/jquery-ui-1.8.6.custom.min.js',
			'css'     => PH_APP_ROOT . '/phifty/web/js/jqueryui/css/flick/jquery-ui-1.8.6.custom.css'
		));

		// jQuery Growl
		$loader->import( array(
			'mapping' => array( PH_APP_ROOT . '/phifty/web' , 'ph'),
			'js' =>  PH_APP_ROOT . '/phifty/web/js/jgrowl/jquery.jgrowl.js',
			'css' => PH_APP_ROOT . '/phifty/web/js/jgrowl/jquery.jgrowl.css'
		));

        # . " lightbox/js/jquery.lightbox-0.5.min.js" 
        # . ' js/lightbox/css/jquery.lightbox-0.5.css'
		# $css_files = 'js/jgrowl/jquery.jgrowl.css'
		# . ' css/action.css'
		# . ' css/pager.css'
		# . ' css/admin.css'
		# . ' css/blueprint/screen.css'
		# . ' css/jquery-region/themes/basic.css';
		$this->loader = $loader;
	}

	function getLoader() {
		return $this->loader;
	}
}
 */

?>
