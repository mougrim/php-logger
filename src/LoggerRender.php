<?php

namespace Mougrim\Logger;

class LoggerRender
{
    const DEFAULT_NULL_MESSAGE = 'null';
    const DEFAULT_TRUE_MESSAGE = 'true';
    const DEFAULT_FALSE_MESSAGE = 'false';

    public static $nullMessage = self::DEFAULT_NULL_MESSAGE;
    public static $trueMessage = self::DEFAULT_TRUE_MESSAGE;
    public static $falseMessage = self::DEFAULT_FALSE_MESSAGE;

    public static function reset()
    {
        static::$nullMessage = static::DEFAULT_NULL_MESSAGE;
        static::$trueMessage = static::DEFAULT_TRUE_MESSAGE;
        static::$falseMessage = static::DEFAULT_FALSE_MESSAGE;
    }

    public static function render($message)
    {
        if (is_null($message)) {
            $rendered = static::$nullMessage;
        } elseif (is_bool($message)) {
            $rendered = $message ? static::$trueMessage : static::$falseMessage;
        } elseif (is_scalar($message)) {
            $rendered = (string) $message;
        } elseif (is_object($message) && method_exists($message, '__toString')) {
            $rendered = $message;
        } else {
            $rendered = print_r($message, 1);
        }

        return $rendered;
    }
}
