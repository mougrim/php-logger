<?php

namespace integration;

use BaseLoggerTestCase;
use Logger;
use LoggerAppenderStream;
use LoggerConfigurator;
use LoggerHierarchy;
use LoggerLayoutSimple;
use LoggerRender;
use stdClass;

class LoggerConfiguratorTest extends BaseLoggerTestCase
{
    public function testConfigure()
    {
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, array(
            'policy' => array(
                'ioError' => 'trigger_error',
                'configurationError' => 'exit'
            ),
            'renderer' => array(
                'nullMessage' => '-',
                'trueMessage' => '1',
                'falseMessage' => '-1',
            ),
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
                    'appenders' => array('stream', array(
                        'class' => 'LoggerAppenderStream',
                        'stream' => 'php://stdout',
                        'useLock' => true,
                        'useLockShortMessage' => false,
                        'minLevel' => 0,
                        'maxLevel' => PHP_INT_MAX,
                        'layout' => 'simple'
                    )),
                    'addictive' => false,
                    'minLevel' => Logger::DEBUG,
                    'maxLevel' => Logger::FATAL,
                ),
            ),
            'root' => array(
                'appenders' => array('stream'),
            )
        ));
        $this->assertArrayHasKey('simple', $hierarchy->getLayoutMap());
        $this->assertArrayHasKey('pattern', $hierarchy->getLayoutMap());
        $this->assertArrayHasKey('stream', $hierarchy->getAppenderMap());
        $this->assertArrayHasKey('logger', $hierarchy->getLoggerMap());

        $this->assertEquals('-', LoggerRender::$nullMessage);
        $this->assertEquals('1', LoggerRender::$trueMessage);
        $this->assertEquals('-1', LoggerRender::$falseMessage);
    }

    public function testInvalidLayout()
    {
        $this->setExpectedException('LoggerException');
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, array(
            'layouts' => array(
                'simple' => array(),
            )
        ));
    }

    public function testInvalidLayoutInAppenderEmpty()
    {
        $this->setExpectedException('LoggerException');
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, array(
            'appenders' => array(
                'stream' => array(
                    'class' => 'LoggerAppenderStream',
                    'stream' => 'php://stdout',
                    'useLock' => true,
                    'useLockShortMessage' => false,
                    'minLevel' => 0,
                    'maxLevel' => PHP_INT_MAX,
                    'layout' => array(),
                ),
            ),
        ));
    }

    public function testInvalidLayoutInAppenderInvalidType()
    {
        $this->setExpectedException('LoggerException');
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, array(
            'appenders' => array(
                'stream' => array(
                    'class' => 'LoggerAppenderStream',
                    'stream' => 'php://stdout',
                    'useLock' => true,
                    'useLockShortMessage' => false,
                    'minLevel' => 0,
                    'maxLevel' => PHP_INT_MAX,
                    'layout' => new stdClass(),
                ),
            ),
        ));
    }

    public function testInvalidAppender()
    {
        $this->setExpectedException('LoggerException');
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, array(
            'appenders' => array(
                'stream' => array(
                    'stream' => 'php://stdout',
                    'useLock' => true,
                    'useLockShortMessage' => false,
                    'minLevel' => 0,
                    'maxLevel' => PHP_INT_MAX,
                ),
            ),
        ));
    }

    public function testInvalidAppenderInLogger()
    {
        $this->setExpectedException('LoggerException');
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, array(
            'loggers' => array(
                'logger' => array(
                    'appenders' => array(new stdClass()),
                    'addictive' => false,
                ),
            ),
        ));
    }

    public function testLogSimple()
    {
        ob_start();
        Logger::configure();
        Logger::getRootLogger()->info('hello world');
        $this->assertEmpty(ob_get_clean());
    }

    public function testLogStream()
    {
        $path = '/tmp/log.txt';
        if (is_file($path)) unlink($path);
        Logger::configure();
        $appender = new LoggerAppenderStream($path);
        $appender->setLayout(new LoggerLayoutSimple());
        Logger::getRootLogger()->addAppender($appender);
        Logger::getRootLogger()->info("hello world");
        $this->assertEquals("root [INFO] - hello world" . PHP_EOL, file_get_contents($path));
    }
}
