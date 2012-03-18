<?php
namespace Phifty\Config;
use SplFileInfo;
use SerializerKit\Serializer;
use Exception;

class ConfigManager
{
    public $environment = 'dev';

    public $stashes = array();

    public function load($section,$file) 
    {
        if( ! file_exists($file) ) {
            throw new Exception("config file $file doesn't exist.");
        }
        $info = new SplFileInfo($file);
        $ext = $info->getExtension();
        $config = array();
        if( $ext === 'yaml' || $ext === 'yml' ) {
            $ser = new Serializer('yaml');
            $config = $ser->decode(file_get_contents($file));
        }
        elseif( $ext === 'php' ) {
            // load php config directly.
            $config = require $file;
        }
        else {
            throw new Exception('Unsupported config file format.');
        }
        $this->stashes[ $section ] = $config;
    }



    /**
     * Allow more useful getter
     *
     * kernel()->config->application  (returns application settings)
     * kernel()->config->framework  (returns framework settings)
     * kernel()->config->database  (returns database settings)
     */
    function __get($name)
    {
        if( isset( $this->stashes[$name][ $this->environment ] )) {
            // It must be an array.
            return new Accessor($this->stashes[$name][ $this->environment ]);
        }
    }

    /**
     * get config from the "config key" like:
     *
     *     mail.user
     *     mail.pass
     *
     * @return hash
     */
    function get($section, $key = null)
    {
        /*
        if( isset( $this->getterCache[ $key ] ) ) 
            return $this->getterCache[ $key ];
         */
        $config = $this->__get( $section );
        if( $key == null ) {
			if( $config )
				return new Accessor($config);
			return null;
		}

		if( isset($config[ $key ]) ) {
			if( is_array( $config[ $key ] ) )
				return new Accessor($config[ $key ]);
            return $config[ $key ];
		}

		if( strchr( $key , '.' ) !== false ) {
			$parts = explode( '.' , $key );
			$ref = $config;
			while( $ref_key = array_shift( $parts ) ) {
				if( ! isset($ref[ $ref_key ]) ) 
					return null;
					# throw new Exception( "Config key: $key not found.  '$ref_key'" );
				$ref = & $ref[ $ref_key ];
			}
			return $ref;
		}
		return null;
	}

}

