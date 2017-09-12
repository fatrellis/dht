<?php
namespace MQK\DHT\Coder;


class Utils
{
    public static function entropy($length=20){
        $str = '';

        for($i=0; $i<$length; $i++)
            $str .= chr(mt_rand(0, 255));

        return $str;
    }
}