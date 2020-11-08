<?php

namespace App;

class AuxiliaryFunctions
{
    public static function ucfirstUtf8($str, $e='utf-8')
    {
        $fc = mb_strtoupper(mb_substr($str, 0, 1, $e), $e);
        return $fc.mb_substr($str, 1, mb_strlen($str, $e), $e);
    }
}
