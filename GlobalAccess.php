<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Base;

class GlobalAccess extends BaseClass {
    protected static function varsList() {
        return ["GLOBALS" => &$GLOBALS, "_SERVER" => &$_SERVER, "_GET" => &$_GET, "_POST" => &$_POST, "_FILES" => &$_FILES, "_COOKIE" => &$_COOKIE, "_SESSION" => &$_SESSION, "_REQUEST" => &$_REQUEST, "_ENV" => &$_ENV];
    }

    protected static function var(string $varName){
        return static::arrayAccessStatic($varName, "varsList");
    }

    private static function _access(string $varName, $key = null, $value = null){
        if(!Arr::isAvailable($var = self::var($varName))){
            return $key === null ? [] : null;
        }
        if($key === null){
            return $var;
        }
        if($rs = Arr::get($key, $var)){
            return $rs;
        }
        $rs = Arr::set($key, $value, $var);
        return $value === null ? null : $rs;
    }

    public static function globals($key = null, $value = null){
        return self::_access("GLOBALS", $key, $value);
    }

    public static function server($key = null, $value = null){
        return self::_access("_SERVER", $key, $value);
    }

    public static function get($key = null, $value = null){
        return self::_access("_GET", $key, $value);
    }

    public static function post($key = null, $value = null){
        return self::_access("_POST", $key, $value);
    }

    public static function files($key = null, $value = null){
        return self::_access("_FILES", $key, $value);
    }

    public static function cookie($key = null, $value = null){
        return self::_access("_COOKIE", $key, $value);
    }

    public static function session($key = null, $value = null){
        return self::_access("_SESSION", $key, $value);
    }

    public static function request($key = null, $value = null){
        return self::_access("_REQUEST", $key, $value);
    }

    public static function env($key = null, $value = null){
        return self::_access("_ENV", $key, $value);
    }
}