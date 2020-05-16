<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Base;

trait TSingleton {
    protected static $instance;

    /**
     * To Prevent The Singleton Cloning Option For Safety
     */
    protected function __clone(){
    }

    /**
     * @return static()
     * @throws \ReflectionException
     */
    public static function instance(){
        // if the initialization params has been changed, the singleton instance will be regenerated
        if(!is_object(static::$instance)) {
            $reflectionObj = new \ReflectionClass(get_called_class());
            $params = func_get_args();
            static::$instance = $reflectionObj->newInstanceArgs($params);
        }

        return static::$instance;
    }
}