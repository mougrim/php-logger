This is a fork of Apache log4php logging library
Main theme - light and fast library, with simple configuring

[![Build Status](https://api.travis-ci.org/mitallast/php-logger.png)](https://travis-ci.org/mitallast/php-logger)

Example of usage, simple hello world

h3. Configuration

```php
Logger::configure(array(
    'policy' => array(
        'ioError' => 'trigger_error', // ignore, trigger_warn, trigger_error, exception or exit
        'configurationError' => 'exception'
    ),
    'layouts' => array(
        'simple' => array(
            'class' => 'LoggerLayoutSimple',
        ),
        'pattern' => array(
            'class' => 'LoggerLayoutPattern',
            'pattern' => '{date:Y/m/d} [{level}] {logger} {location:file:line, class.function} {mdc:key} {mdc} {ndc}: {message} {ex}',
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
```
h3. Logging

```php
Logger::getLogger('logger')->trace("hello world");
Logger::getLogger('logger')->debug("hello world");
Logger::getLogger('logger')->info("hello world");
Logger::getLogger('logger')->warn("hello world");
Logger::getLogger('logger')->fatal("hello world");
Logger::getLogger('logger')->log(Logger::DEBUG, "hello world");
```

h3. Mapped Diagnostic Context, mdc

```php
LoggerMDC::put("request", $i++);
Logger::getLogger('accesslog')->info("new request");
Logger::getLogger('db')->info("execute sql");

# in some web log : cat log | grep 12412
accesslog [request=12412] new request
db [request=12412] execute sql
```

h3. Timer

```php
$logger = Logger::getLogger('db');
$timer = $logger->timer();
$pdo->exec('select * from foo');
$timer->info("sql executed at {time}"); // rendered at
```