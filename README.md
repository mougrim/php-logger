This is a fork of Apache log4php logging library
Main theme - light and fast library, with simple configuring

[![Build Status](https://api.travis-ci.org/mitallast/php-logger.png)](https://travis-ci.org/mitallast/php-logger)

Example of usage, simple hello world

    Logger::configure(array(
        'layouts' => array(
            'simple' => array(
                'class' => 'LoggerLayoutSimple',
            ),
            'pattern' => array(
                'class' => 'LoggerLayoutPattern',
                'pattern' => '{date:Y/m/d} [{level}] {logger} {file}:{line} {class}:{function} {mdc:key} {mdc} {ndc}: {message} {ex}',
            ),
        ),
        'appenders' => array(
            'stream' => array(
                'class' => 'LoggerAppenderStream',
                'stream' => 'php://stdout',
                'useLock' => true,
                'useLockShortMessage' => false,
                'minLevel' => Logger::DEBUG,
                'maxLevel' => Logger::FATAL,
                'layout' => 'simple',
            ),
        ),
        'loggers' => array(
            'logger' => array(
                'appenders' => array('stream'),
                'addictive' => false,
                'minLevel' => Logger::TRACE,
                'maxLevel' => Logger::FATAL,
            ),
        ),
        'root' => array(
            'appenders' => array('stream'),
        )
    ));
    Logger::getLogger('logger')->info("hello world");
