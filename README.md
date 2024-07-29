# Repository is archived
If you want to maintain it, you can make a fork.

# php-logger

This is a fork of Apache log4php logging library.

Main theme - light and fast library, with simple configuring.

If you want use classes without namespaces, see [mougrim/php-logger-old-interface](https://github.com/mougrim/php-logger-old-interface).

See [changelog](CHANGELOG.md) and [upgrade instructions](UPGRADE.md).

[![Latest Stable Version](https://poser.pugx.org/mougrim/php-logger/version)](https://packagist.org/packages/mougrim/php-logger)
[![Latest Unstable Version](https://poser.pugx.org/mougrim/php-logger/v/unstable)](https://packagist.org/packages/mougrim/php-logger)
[![License](https://poser.pugx.org/mougrim/php-logger/license)](https://packagist.org/packages/mougrim/php-logger)
[![Build Status](https://api.travis-ci.org/mougrim/php-logger.png?branch=master)](https://travis-ci.org/mougrim/php-logger)
[![Test Coverage](https://codeclimate.com/github/mougrim/php-logger/badges/coverage.svg)](https://codeclimate.com/github/mougrim/php-logger/coverage)

Configuration
-------------

```php
use Mougrim\Logger\Appender\AppenderStream;
use Mougrim\Logger\Layout\LayoutPattern;
use Mougrim\Logger\Layout\LayoutSimple;
use Mougrim\Logger\Logger;

Logger::configure([
    'policy' => [
        'ioError' => 'trigger_error', // ignore, trigger_warn, trigger_error, exception or exit
        'configurationError' => 'exception'
    ],
    'layouts' => [
        'simple' => [
            'class' => LayoutSimple::class,
        ],
        'pattern' => [
            'class' => LayoutPattern::class,
            'pattern' => '{date:Y/m/d} [{level}] {logger} {location:file:line, class.function} {mdc:key} {mdc} {ndc}: {message} {ex}',
        ],
    ],
    'appenders' => [
        'stream' => [
            'class' => AppenderStream::class,
            'stream' => 'php://stdout',
            // pass useLock and useLockShortMessage true for enable lock
            'useLock' => false,
            'useLockShortMessage' => false,
            'minLevel' => Logger::DEBUG,
            'maxLevel' => Logger::FATAL,
            'layout' => 'simple',
        ],
    ],
    'loggers' => [
        'logger' => [
            'appenders' => ['stream'],
            'addictive' => false,
            'minLevel' => Logger::TRACE,
            'maxLevel' => Logger::FATAL,
        ],
    ],
    'root' => [
        'appenders' => ['stream'],
    ]
]);
```
Logging
-------

```php
Logger::getLogger('logger')->trace("hello world");
Logger::getLogger('logger')->debug("hello world");
Logger::getLogger('logger')->info("hello world");
Logger::getLogger('logger')->warn("hello world");
Logger::getLogger('logger')->fatal("hello world");
Logger::getLogger('logger')->log(Logger::DEBUG, "hello world");
```

Mapped Diagnostic Context, mdc
------------------------------

```php
LoggerMDC::put("request", $i++);
Logger::getLogger('accesslog')->info("new request");
Logger::getLogger('db')->info("execute sql");

# in some web log : cat log | grep 12412
accesslog [request=12412] new request
db [request=12412] execute sql
```

Timer
-----

```php
$logger = Logger::getLogger('db');
$timer = $logger->timer();
$pdo->exec('select * from foo');
$timer->info("sql executed at {time}"); // rendered at
```

Thanks
-----

Thanks to [mitallast](https://github.com/mitallast/) for the development of the main part of this project.
