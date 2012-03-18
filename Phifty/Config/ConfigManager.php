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
        $ser = new Serializer;
        $config = array();
        if( $ext === 'yaml' || $ext === 'yml' ) {
            $ser->setFormat('yaml');
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


    function __get($name)
    {
        if( isset( $this->stashes[$name][ $this->environment ] )) {
            return $this->stashes[$name][ $this->environment ];
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
        if( $key == null )
            return new Accessor($config);

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

