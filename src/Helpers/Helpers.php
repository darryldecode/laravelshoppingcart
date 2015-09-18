<?php

namespace Ozanmuyes\Cart\Helpers;

class Helpers
{
    /**
     * normalize price
     *
     * @param $price
     * @return float
     */
    public static function normalizePrice($price)
    {
        return (is_string($price))
                ? floatval($price)
                : $price;
    }

    /**
     * check if array is multi dimensional array
     * This will only check the first element of the array if it is still an array
     * to decide that it is a multi dimensional, if you want to check the array strictly
     * with all on its element, flag the second argument as true
     *
     * @param $array
     * @param bool $recursive
     * @return bool
     */
    public static function isMultiArray($array, $recursive = false)
    {
        if ($recursive) {
            return (count($array) == count($array, COUNT_RECURSIVE))
                    ? false
                    : true;
        } else {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }

    /**
     * check if variable is set and has value, return a default value
     *
     * @param $var
     * @param bool|mixed $default
     * @return bool|mixed
     */
    public static function issetAndHasValueOrAssignDefault(&$var, $default = false)
    {
        return (isset($var) && $var != "")
                ? $var
                : $default;
    }
}
