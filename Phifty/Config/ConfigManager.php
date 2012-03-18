<?php
namespace Phifty\Config;
use SplFileInfo;
use SerializerKit\Serializer;
use Exception;

class ConfigManager
{
    public $stashes = array();

    public function load($section,$file) 
    {
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
            throw new Exception("Unsupported config file format.");
        }
        $this->stashes[ $section ] = $stashes;
    }


}

