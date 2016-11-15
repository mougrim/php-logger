<?php

namespace Mougrim\Logger\integration;

use Mougrim\Logger\Appender\AppenderStream;
use Mougrim\Logger\BaseLoggerTestCase;
use Mougrim\Logger\Layout\LayoutInterface;
use Mougrim\Logger\Layout\LayoutPattern;
use Mougrim\Logger\Layout\LayoutSimple;
use Mougrim\Logger\Layout\Pattern\PatternLogger;
use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerConfigurator;
use Mougrim\Logger\LoggerException;
use Mougrim\Logger\LoggerHierarchy;
use Mougrim\Logger\LoggerRender;
use stdClass;

class LoggerConfiguratorTest extends BaseLoggerTestCase
{
    public function testConfigure()
    {
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, [
            'policy' => [
                'ioError' => 'trigger_error',
                'configurationError' => 'exit',
            ],
            'renderer' => [
                'nullMessage' => '-',
                'trueMessage' => '1',
                'falseMessage' => '-1',
            ],
            'layouts' => [
                'simple' => [
                    'class' => LayoutSimple::class,
                ],
                'pattern' => [
                    'class' => LayoutPattern::class,
                    'pattern' => '{date:Y/m/d} [{level}] {logger} {file}:{line} {class}:{function} {mdc:key} {mdc} {ndc}: {message} {ex}',
                ],
                'additional_pattern' => [
                    'class' => LayoutPattern::class,
                    'pattern' => '{additional_logger}',
                    'additionalPatternMap' => [
                        'additional_logger' => PatternLogger::class,
                    ],
                ],
            ],
            'appenders' => [
                'stream' => [
                    'class' => AppenderStream::class,
                    'stream' => 'php://stdout',
                    'useLock' => false,
                    'minLevel' => 0,
                    'maxLevel' => PHP_INT_MAX,
                    'layout' => 'simple',
                ],
            ],
            'loggers' => [
                'logger' => [
                    'appenders' => [
                        'stream', [
                        'class' => AppenderStream::class,
                        'stream' => 'php://stdout',
                        'useLock' => false,
                        'minLevel' => 0,
                        'maxLevel' => PHP_INT_MAX,
                        'layout' => 'simple',
                        ],
                    ],
                    'addictive' => false,
                    'minLevel' => Logger::DEBUG,
                    'maxLevel' => Logger::FATAL,
                ],
            ],
            'root' => [
                'appenders' => ['stream'],
            ],
        ]);
        $this->assertArrayHasKey('simple', $hierarchy->getLayoutMap());
        $this->assertArrayHasKey('pattern', $hierarchy->getLayoutMap());
        $this->assertArrayHasKey('additional_pattern', $hierarchy->getLayoutMap());
        /** @var LayoutInterface $additional_pattern_layout */
        $additional_pattern_layout = $hierarchy->getLayoutMap()['additional_pattern'];
        $this->assertSame(
            'test'.PHP_EOL,
            $additional_pattern_layout->formatMessage($hierarchy->getLogger('test'), Logger::INFO, 'message')
        );
        $this->assertArrayHasKey('stream', $hierarchy->getAppenderMap());
        $this->assertArrayHasKey('logger', $hierarchy->getLoggerMap());

        $this->assertSame('-', LoggerRender::$nullMessage);
        $this->assertSame('1', LoggerRender::$trueMessage);
        $this->assertSame('-1', LoggerRender::$falseMessage);
    }

    public function testInvalidLayout()
    {
        $this->setExpectedException(LoggerException::class);
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, [
            'layouts' => [
                'simple' => [],
            ],
        ]);
    }

    public function testInvalidLayoutInAppenderEmpty()
    {
        $this->setExpectedException(LoggerException::class);
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, [
            'appenders' => [
                'stream' => [
                    'class' => AppenderStream::class,
                    'stream' => 'php://stdout',
                    'useLock' => false,
                    'minLevel' => 0,
                    'maxLevel' => PHP_INT_MAX,
                    'layout' => [],
                ],
            ],
        ]);
    }

    public function testInvalidLayoutInAppenderInvalidType()
    {
        $this->setExpectedException(LoggerException::class);
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, [
            'appenders' => [
                'stream' => [
                    'class' => AppenderStream::class,
                    'stream' => 'php://stdout',
                    'useLock' => false,
                    'minLevel' => 0,
                    'maxLevel' => PHP_INT_MAX,
                    'layout' => new stdClass(),
                ],
            ],
        ]);
    }

    public function testInvalidAppender()
    {
        $this->setExpectedException(LoggerException::class);
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, [
            'appenders' => [
                'stream' => [
                    'stream' => 'php://stdout',
                    'useLock' => false,
                    'minLevel' => 0,
                    'maxLevel' => PHP_INT_MAX,
                ],
            ],
        ]);
    }

    public function testInvalidAppenderInLogger()
    {
        $this->setExpectedException(LoggerException::class);
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, [
            'loggers' => [
                'logger' => [
                    'appenders' => [new stdClass()],
                    'addictive' => false,
                ],
            ],
        ]);
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
        if (is_file($path)) {
            unlink($path);
        }
        Logger::configure();
        $appender = new AppenderStream($path);
        $appender->setLayout(new LayoutSimple());
        Logger::getRootLogger()->addAppender($appender);
        Logger::getRootLogger()->info('hello world');
        $this->assertSame('root [INFO] - hello world'.PHP_EOL, file_get_contents($path));
    }
}
