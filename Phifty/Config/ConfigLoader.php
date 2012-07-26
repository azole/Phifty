<?php
namespace Phifty\Config;
use SplFileInfo;
use Exception;

class ConfigLoader
{
    public $stashes = array();


    public function load($section,$file) 
    {
        if( ! file_exists($file) ) {
            throw new Exception("config file $file doesn't exist.");
        }
        $info = new SplFileInfo($file);
        // $ext = $info->getExtension();

        $parts = explode('.',$file);
        $ext = end($parts);

        $config = array();
        if( $ext === 'yaml' || $ext === 'yml' ) {
            // if we have yaml extension, just decode config file with extension
            if( extension_loaded('yaml') ) {
                return $this->stashes[ $section ] = yaml_parse_file($file);
            } else {
                $ser = new \SerializerKit\Serializer('yaml');
                return $this->stashes[ $section ] = $ser->decode(file_get_contents($file));
            }
        }
        elseif( $ext === 'php' ) {
            // load php config directly.
            return $this->stashes[ $section ] = require $file;
        }
        else {
            throw new Exception('Unsupported config file format.');
        }
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
        if( isset( $this->stashes[$name] )) {
            // It must be an array.
            return new Accessor($this->stashes[$name]);
        }
    }



    /**
     * get section stash, returns stash in pure php array.
     *
     * @return array
     */
    function getSection($name)
    {
        if( isset( $this->stashes[$name] )) {
            // It must be an array.
            return $this->stashes[$name];
        }
    }


    /**
     * get config from the "config key" like:
     *
     *   mail.user
     *   mail.pass
     *
     * @return array
     */
    function get($section, $key = null)
    {
        /*
        if( isset( $this->getterCache[ $key ] ) ) 
            return $this->getterCache[ $key ];
         */
        $config = $this->getSection( $section );
        if( $key == null ) 
        {
            if( ! empty($config) )
                return new Accessor($config);
            return null;
        }

        if( isset($config[ $key ]) ) 
        {
            if( is_array( $config[ $key ] ) ) {
                if( empty($config[ $key ]) )
                    return null;
                return new Accessor($config[ $key ]);
            }
            return $config[ $key ];
        }

        if( false !== strchr( $key , '.' ) ) 
        {
            $parts = explode( '.' , $key );
            $ref = $config;
            while( $ref_key = array_shift( $parts ) ) {
                if( ! isset($ref[ $ref_key ]) )
                    return null;
                $ref = & $ref[ $ref_key ];
            }

            if( is_array( $ref ) ) {
                if( empty($ref) )
                    return null;
                return new Accessor($ref);
            }
            return $ref;
        }
        return null;
    }

    public function isLoaded($sectionId) {
        return isset($this->stashes[$sectionId]);
    }

}

