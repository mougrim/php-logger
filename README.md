This is a fork of Apache log4php logging library
Main theme - light and fast library, with simple configuring

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
                'minLevel' => 0,
                'maxLevel' => PHP_INT_MAX,
                'layout' => 'simple',
            ),
        ),
        'loggers' => array(
            'logger' => array(
                'appenders' => array('stream'),
                'addictive' => false,
            ),
        ),
        'root' => array(
            'appenders' => array('stream'),
        )
    ));
    Logger::getLogger('logger')->info("hello world");
