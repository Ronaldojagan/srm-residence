<?php

namespace App\Enums;

use ReflectionClass;

abstract class Enum
{
    /**
     * cache reflection request
     * @var array
     */
    protected static $constCache = [];
    public static function getConstants()
    {
        if (empty(static::$constCache[get_called_class()])) {
            $reflect = new ReflectionClass(get_called_class());
            static::$constCache[get_called_class()] = $reflect->getConstants();
        }

        return static::$constCache[get_called_class()];
    }

    /**
     * get only scalar class constant array
     *
     * @param array $except
     *
     * @return array
     */
    public static function getScalarConstants(array $except = [])
    {
        $scalarConstants = array_filter(static::getConstants(), function ($constant) use ($except) {
            return is_scalar($constant) && !in_array($constant, $except);
        });

        return $scalarConstants;
    }
}
