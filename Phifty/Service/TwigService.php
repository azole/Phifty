<?php
namespace Phifty\Service;
use Twig_Environment;
use Twig_Loader_Filesystem;
use Twig_Function_Function;
use Twig_Loader_String;

use Twig_Extension_Debug;
use Twig_Extension_Optimizer;
use Twig_Extensions_Extension_Text;
use Twig_Extensions_Extension_I18n;

class TwigService
    implements ServiceInterface
{
    public function getId() { return 'Twig'; }

    public function register($kernel, $options = array() )
    {
        $kernel->twig = function() use($kernel, $options) {
            $templateDirs = array();
            if ( isset($options['TemplateDirs']) ) {
                foreach( $options['TemplateDirs'] as $dir ) {
                    // use absolute path from app root
                    $templateDirs[] = PH_APP_ROOT . DIRECTORY_SEPARATOR . $dir;
                }
            }
            // append fallback template dirs from plugin dir or framework plugin dir.
            $templateDirs[] = $kernel->appPluginDir;
            $templateDirs[] = $kernel->frameworkPluginDir;

            // create the filesystem loader
            $loader   = new Twig_Loader_Filesystem( $templateDirs );


            // build default environment arguments
            $args = array(
                'cache' => kernel()->getCacheDir() . DIRECTORY_SEPARATOR . 'twig'
            );

            if ($kernel->isDev) {
                $args['debug'] = true;
                $args['auto_reload'] = true;
            } else {
                // for production
                $args['optimizations'] = true;
            }

            // override from config
            if ( isset($options['Environment']) ) {
                $args = array_merge( $args , $options['Environment'] );
            }

            // http://www.twig-project.org/doc/api.html#environment-options
            $env = new Twig_Environment($loader, $args);
            return (object) array(
                'loader' => $loader,
                'env' => $env,
            );
        };
    }
}
