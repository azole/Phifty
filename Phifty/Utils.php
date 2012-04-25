<?php

namespace Phifty;

class Utils
{

    static function system($command)
    {
        system( $command ) !== false or die('execution failed.');
    }

    static function rrmdir($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file)
            if ($file != "." && $file != "..") static::rrmdir("$dir/$file");
            rmdir($dir);
        }
        else if (file_exists($dir)) unlink($dir);
    } 

    static function rcopy($src, $dst) {
        if (file_exists($dst)) static::rrmdir($dst);
        if (is_dir($src)) {
            mkdir($dst);
            $files = scandir($src);
            foreach ($files as $file)
            if ($file != "." && $file != "..") static::rcopy("$src/$file", "$dst/$file"); 
        }
        else if (file_exists($src)) copy($src, $dst);
    }


    static function array_get_rand( $elems ) 
    {
        return $elems[ array_rand( $elems ) ];
    }

    // recursive
    static function array_to_object($array) 
    {

        if(!is_array($array))
            return $array;

        $object = new \stdClass();
        if (is_array($array) && count($array) > 0) 
        {

            foreach ($array as $name=>$value) 
            {
                $name = strtolower(trim($name));
                if (!empty($name))
                    $object->$name = self::array_to_object($value);

            }

            return $object; 

        }
        else {
            return FALSE;
        }
    }

}

?>
