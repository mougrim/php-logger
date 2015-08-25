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
        self::$nullMessage = self::DEFAULT_NULL_MESSAGE;
        self::$trueMessage = self::DEFAULT_TRUE_MESSAGE;
        self::$falseMessage = self::DEFAULT_FALSE_MESSAGE;
    }

    public static function render($message)
    {
        if (is_null($message)) {
            $rendered = self::$nullMessage;
        } else if (is_bool($message)) {
            $rendered = $message ? self::$trueMessage : self::$falseMessage;
        } else if (is_scalar($message)) {
            $rendered = (string)$message;
        } else if (is_object($message) && method_exists($message, '__toString')) {
            $rendered = $message;
        } else {
            $rendered = print_r($message, 1);
        }
        return $rendered;
    }
}
