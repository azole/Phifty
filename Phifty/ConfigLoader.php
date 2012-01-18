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
use YAMLKit\YAML;

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
        $this->environment = getenv('PHIFTY_ENV');
        if( $this->environment == null ) {
            if( isset($_REQUEST['PHIFTY_ENV']) ) {
                $this->environment = $_REQUEST['PHIFTY_ENV'];
            }
        }
        if( $this->environment == null ) {
            $this->environment = 'dev';
        }

        switch( $this->environment ) {
            case 'test':
            case 'dev':
                $this->config = $this->loadEnvironmentConfig( $app,$this->environment );
                break;
            case 'prod':
                $configKey = 'config_' . __FILE__;

                // check cache
                if(( $config = $app->cache->get( $configKey )) != null ) {
                    $this->config = $config;
                }
                else {
                    $this->config = $this->loadEnvironmentConfig( $app,$this->environment );
                    $app->cache->set( $configKey , $this->config );
                }
                break;
            default:
                throw new Exception("Unsupported environment mode: {$this->environment}.");
                break;
        }
    }

    function loadEnvironmentConfig($app,$environment)
    {
        // load config by env
        $appConfigFile = $app->rootDir . DIRECTORY_SEPARATOR . 'config' 
                . DIRECTORY_SEPARATOR . 'app.yml';
        $appSiteConfigFile = $app->rootDir . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . 'app_site.yml';
        $appConfig = null;
        if ( file_exists( $appSiteConfigFile ) ) {
            $appConfig = YAML::loadFile( $appSiteConfigFile );
        }
        elseif ( ! $appConfig && file_exists( $appConfigFile ) ) {
            $appConfig = YAML::loadFile( $appConfigFile );
        }

        if( ! $appConfig )
            throw new Exception( "Application config is empty.. Can not load $appSiteConfigFile and $appConfigFile" );

        // environment name, will be 'dev', 'testing', 'prod' ...
        // $environment = $this->environment = $appConfig['environment']; 

        // load environment config 
        $envConfigFile = $app->rootDir . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . $environment . '.yml';
        $envSiteConfigFile = $app->rootDir . DIRECTORY_SEPARATOR . 'config'
                . DIRECTORY_SEPARATOR . $environment . '_site.yml';

        if( ! file_exists( $envConfigFile ) )
            throw new Exception( "$envConfigFile not found." );

        $envConfig = YAML::loadFile( $envConfigFile );

        // it site environment config file is defined, merge it!
        if( file_exists( $envSiteConfigFile ) ) {
            $envSiteConfig = YAML::loadFile( $envSiteConfigFile );
            $envConfig = array_merge( $envConfig , $envSiteConfig );
        }

        // merge environment config to app config
        return array_merge( $appConfig , (array) $envConfig );

        // not used currently
        // $this->appConfigFile     = $appConfigFile;
        // $this->appSiteConfigFile = $appSiteConfigFile;
        // $this->envConfigFile     = $envConfigFile;
    }


    function getEnvironment()
    {
        return $this->environment;
    }

    function isDevelopment()
    {
        return $this->environment === 'dev';
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


