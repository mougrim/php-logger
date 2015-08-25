<?php
namespace Mougrim\Logger;

class LoggerMDC
{
    private static $map = [];

    public static function put($key, $context)
    {
        $key = (string)$key;
        $context = (string)$context;
        self::$map[$key] = $context;
    }

    public static function remove($key)
    {
        $key = (string)$key;
        unset(self::$map[$key]);
    }

    public static function get($key)
    {
        $key = (string)$key;
        if (array_key_exists($key, self::$map)) {
            return self::$map[$key];
        } else {
            return null;
        }
    }

    public static function getMap()
    {
        return self::$map;
    }

    public static function clear()
    {
        self::$map = [];
    }
}
