<?php
namespace Mougrim\Logger;

use Mougrim\Logger\Appender\AppenderStd;
use Mougrim\Logger\Layout\LayoutSimple;

class LoggerHierarchyTest extends BaseLoggerTestCase
{
    public function testGetRootLoggerEmpty()
    {
        $hierarchy = new LoggerHierarchy();
        $logger = $hierarchy->getRootLogger();
        $this->assertEquals('root', $logger->getName());
    }

    public function testGetRootLoggerExisted()
    {
        $logger = new Logger('root');
        $hierarchy = new LoggerHierarchy();
        $hierarchy->setRootLogger($logger);
        $this->assertTrue($logger === $hierarchy->getRootLogger());
        $this->assertEquals([], $hierarchy->getAppenderMap());
    }

    public function testGetLoggerCreate()
    {
        $rootLogger = new Logger('root');
        $hierarchy = new LoggerHierarchy();
        $hierarchy->setRootLogger($rootLogger);

        $logger = $hierarchy->getLogger('newLogger');
        $this->assertEquals('newLogger', $logger->getName());
        $this->assertEquals($rootLogger, $logger->getParent());
        $this->assertEquals(['newLogger' => $logger], $hierarchy->getLoggerMap());
    }

    public function testGetAppenderNotExists()
    {
        $this->setExpectedException(LoggerException::class);
        $hierarchy = new LoggerHierarchy();
        $hierarchy->getAppender('new');
    }

    public function testGetAppenderExists()
    {
        $hierarchy = new LoggerHierarchy();
        $appender = new AppenderStd();
        $hierarchy->setAppender('appender', $appender);
        $this->assertEquals($appender, $hierarchy->getAppender('appender'));
        $this->assertEquals(['appender' => $appender], $hierarchy->getAppenderMap());
    }

    public function testGetLayoutNotExists()
    {
        $this->setExpectedException(LoggerException::class);
        $hierarchy = new LoggerHierarchy();
        $hierarchy->getLayout('new');
    }

    public function testGetLayoutExists()
    {
        $hierarchy = new LoggerHierarchy();
        $layout = new LayoutSimple();
        $hierarchy->setLayout('simple', $layout);
        $this->assertEquals($layout, $hierarchy->getLayout('simple'));
        $this->assertEquals(['simple' => $layout], $hierarchy->getLayoutMap());
    }
}
