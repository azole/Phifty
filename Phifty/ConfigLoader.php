<?php
/*

YAML config content

$loader = new ConfigLoader;
$loader->load( $defaultFile );
$loader->load( $file , $override ); // app config
$loader->load( $siteFile , $override ); // config not in version control
$loader->get( $key );
	$loader->get('sitename');
	$loader->get('mail');
	$loader->get('i18n');

$loader->get('i18n.setup.blah'); // better way

*/
namespace Phifty;
use Spyc;
use Exception;


/*
    
$loader = new ConfigLoader;
$loader->load( $defaultFile );
$loader->load( $file , $override ); // app config
$loader->load( $siteFile , $override ); // config not in version control
$loader->get( $key );
	$loader->get('sitename');
	$loader->get('mail');
	$loader->get('i18n');

*/
use Phifty\Cache\YAML;

class ConfigLoader
{
    private $getterCache = array();

    public $config;

    public $appConfigFile,
            $appSiteConfigFile,
            $envConfigFile;

    public $environment;
    public $development;

    /* yaml cache */
    public $yCache;

    function __construct($app)
    {

        // TODO: Try to merge a cached version config data.

        $appConfigFile = $app->rootDir . DIRECTORY_SEPARATOR . 'config' 
                . DIRECTORY_SEPARATOR . 'app.yml';
        $appSiteConfigFile = $app->rootDir . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . 'app_site.yml';

        $yCache = null;
        $yCache = $this->yCache = new YAML( array( 
            'cache_dir'  => $app->rootDir . DIRECTORY_SEPARATOR . 'cache/config' 
        ));

        $appConfig = null;
        if ( file_exists( $appSiteConfigFile ) ) {
            $appConfig = $yCache->loadFile( $appSiteConfigFile );
        }
        if ( ! $appConfig && file_exists( $appConfigFile ) ) {
            $appConfig = $yCache->loadFile( $appConfigFile );
        }

        if( ! $appConfig )
            throw new Exception( "Application config is so empty.. Can not load $appSiteConfigFile and $appConfigFile" );

        // environment name, will be 'dev', 'testing', 'prod' ...
        $environment = $this->environment = $appConfig['environment']; 


        // load environment config 
        $envConfigFile = $app->rootDir . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . $environment . '.yml';

        $envSiteConfigFile = $app->rootDir . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . $environment . '_site.yml';

        $envConfig = $yCache->loadFile( $envConfigFile );

        // it site environment config file is defined, merge it!
        if( file_exists( $envSiteConfigFile ) ) {
            $envSiteConfig = $yCache->loadFile( $envSiteConfigFile );
            $envConfig = array_merge( $envConfig , $envSiteConfig );
        }


        // merge back to app config
        $appConfig = array_merge( $appConfig , (array) $envConfig );
        $this->appConfigFile     = $appConfigFile;
        $this->appSiteConfigFile = $appSiteConfigFile;
        $this->envConfigFile     = $envConfigFile;
        $this->config            = $appConfig;


        // not in development
        if( ! $this->isDevelopment() ) {
            // cache it

        }
    }

    function getEnvironment()
    {
        return $this->environment;
    }

    function isDevelopment()
    {
        return @$this->config['development'];
    }


    /*
     * get config from the "config key" like:
     *
     *     mail.user
     *     mail.pass
     *
     * @return hash
     */
    function get( $key ) 
    {
        if( isset( $this->getterCache[ $key ] ) ) 
            return $this->getterCache[ $key ];

		if( isset($this->config[ $key ]) ) {
			if( is_array( $this->config[ $key ] ) )
				return (object) $this->config[ $key ];
            return $this->getterCache[ $key ] = $this->config[ $key ];
		}

		if( strchr( $key , '.' ) !== false ) {
			$parts = explode( '.' , $key );
			$ref = $this->config;
			while( $ref_key = array_shift( $parts ) ) {
				if( ! isset($ref[ $ref_key ]) ) 
					return null;
					# throw new Exception( "Config key: $key not found.  '$ref_key'" );
				$ref = & $ref[ $ref_key ];
			}
			return $this->getterCache[ $key ] = $ref;
		}
		return null;
	}


    /* 
     * import file to config section.
     *
     * section name is optional, if it's not specified,
     * merge it into the main config hash.
     */
    function import( $file , $section = null )
    {
        if( $section )
            return $this->config[ $section ] = $this->yCache->loadFile( $file );
        return $this->config = array_merge( $this->config , $this->yCache->loadFile( $file ) );
    }

	function getConfig()
   	{
		return $this->config;
	}

    function __tostring()
    {
        return YAML::Dump( $this->config );
    }

	function isEmpty()
	{
		return empty( $this->config );
	}


}


