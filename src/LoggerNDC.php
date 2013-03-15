<?php

class LoggerNDC
{
    private static $stack = array();

    public static function push($context)
    {
        $context = (string)$context;
        array_push(self::$stack, $context);
    }

    public static function pop()
    {
        array_pop(self::$stack);
    }

    public static function getStack()
    {
        return self::$stack;
    }

    public static function clear()
    {
        self::$stack = array();
    }
}