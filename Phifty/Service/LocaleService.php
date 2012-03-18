<?php
namespace Phifty\Service;
use Phifty\L10N;

class LocaleService
    implements ServiceInterface
{
    public function register($kernel, $options = array() )
    {
        $locale = new L10N;
        $i18nConfig = $kernel->config->get('framework','i18n');

        if( $i18nConfig ) {
            // var_dump( $i18nConfig ); 
            // var_dump( $_SESSION ); 
            $locale->setDefault( $i18nConfig->default );
            $locale->domain( $this->appId ); # use application id for domain name.

            $localeDir = $this->getRootDir() . DIRECTORY_SEPARATOR . $i18nConfig->localedir;

            $locale->localedir( $localeDir );

            /* add languages to list */
            foreach( @$i18nConfig->lang as $localeName ) {
                $locale->add( $localeName );
            }

            $locale->init();
            # _('en');
        }
        
        $kernel->locale = function() use ($locale) {
            return $locale;
        };
    }
}
