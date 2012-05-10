<?php
namespace Phifty\Service;
use Phifty\Locale;

class LocaleService
    implements ServiceInterface
{

    public function getId() { return 'Locale'; }

    public function register($kernel, $options = array() )
    {
        // call spl autoload, to load `__` locale function
        class_exists('Phifty\Locale', true);

        $config = $kernel->config->get('framework','Locale');
        if( ! $config )
            return;
        
        $kernel->locale = function() use ($kernel,$config) {

            $textdomain =  $kernel->config->framework->ApplicationID;
            $defaultLang  = $config->Default ?: 'en';
            $localeDir = $config->LocaleDir;

            if( ! ( $textdomain && $defaultLang && $localeDir) ) {
                return;
            }

            $locale = new Locale;
            $locale->setDefault( $defaultLang );
            $locale->domain( $textdomain ); # use application id for domain name.
            $locale->localedir( $kernel->rootDir . DIRECTORY_SEPARATOR . $localeDir);

            // add languages to list
            foreach( @$config->Langs as $localeName ) {
                $locale->add( $localeName );
            }

            # _('en');
            $locale->init();

            return $locale;
        };
    }
}
