<?php

spl_autoload_register(function ($class) {
    if (strpos($class, 'LoggerAppender') === 0) {
        return require_once __DIR__ . DIRECTORY_SEPARATOR . 'appenders' . DIRECTORY_SEPARATOR . $class . '.php';
    } elseif (strpos($class, 'LoggerLayout') === 0) {
        return require_once __DIR__ . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $class . '.php';
    } elseif (strpos($class, 'Logger') === 0) {
        return require_once __DIR__ . DIRECTORY_SEPARATOR . $class . '.php';
    } else {
        return false;
    }
});
