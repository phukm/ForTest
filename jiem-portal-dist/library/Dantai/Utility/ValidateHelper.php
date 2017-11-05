<?php
/**
 * Created by PhpStorm.
 * User: ManhLN
 * Date: 08/11/2016
 * Time: 10:54
 */

namespace Dantai\Utility;


class ValidateHelper
{
    public static function isNumeric($value)
    {
        return preg_match('/^[0-9]*$/', $value);
    }

    public static function isYear($value){
        return preg_match('/^[0-9]{4}$/', $value);
    }
}