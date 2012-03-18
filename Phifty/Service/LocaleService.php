<?php
namespace Phifty\Service;
use Phifty\L10N;

class LocaleService
    implements ServiceInterface
{

    public function register($kernel, $options = array() )
    {
        // call spl autoload, to load `__` i18n function
        class_exists('Phifty\L10N', true);

        $config = $kernel->config->get('framework','i18n');
        if( $config->isEmpty() )
            return;
        if( null == $config->default )
            return;

        $locale = new L10N;
        $locale->setDefault( $config->default );
        $locale->domain( $kernel->config->application['namespace'] ); # use application id for domain name.
        $localeDir = $kernel->getRootDir() . DIRECTORY_SEPARATOR . $config->localedir;

        $locale->localedir( $localeDir );

        // add languages to list
        foreach( @$config->lang as $localeName ) {
            $locale->add( $localeName );
        }

        $locale->init();
        # _('en');
        
        $kernel->locale = function() use ($locale) {
            return $locale;
        };
    }
}
