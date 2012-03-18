<?php
namespace Phifty\Service;
use Phifty\L10N;

class LocaleService
    implements ServiceInterface
{


    public function register($kernel, $options = array() )
    {
        $config = $kernel->config->framework['i18n'];

        if( null == $config )
            return;

        $locale = new L10N;
        $locale->setDefault( $config->default );
        $locale->domain( $kernel->config->application['namespace'] ); # use application id for domain name.
        $localeDir = $kernel->getRootDir() . DIRECTORY_SEPARATOR . $config->localedir;

        $locale->localedir( $localeDir );

        /* add languages to list */
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
