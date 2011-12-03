<?php

namespace Phifty;

class Utils
{

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
