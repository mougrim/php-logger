<?php

class LoggerRender
{
    public static function render($message)
    {
        if (is_null($message)) {
            $rendered = 'null';
        } else if (is_bool($message)) {
            $rendered = $message ? 'true' : 'false';
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
