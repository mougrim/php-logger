<?php

class LoggerTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $root = new Logger('root');
        $logger = new Logger('logger', $root);
        $this->assertEquals('logger', $logger->getName());
        $this->assertTrue($root === $logger->getParent());
        $this->assertEquals(array(), $logger->getAppenders());
        $this->assertTrue($logger->getAddictive());
    }

    public function testAppender()
    {
        $logger = new Logger('logger');
        /** @var $appender LoggerAppenderAbstract */
        $appender = $this->getMockForAbstractClass('LoggerAppenderAbstract');
        /** @var $appenderSecond LoggerAppenderAbstract */
        $appenderSecond = clone $appender;

        $this->assertEquals(array(), $logger->getAppenders());

        $logger->addAppender($appender);
        $this->assertEquals(array($appender), $logger->getAppenders());
        $logger->addAppender($appender);
        $this->assertEquals(array($appender), $logger->getAppenders());

        $logger->addAppender($appenderSecond);
        $this->assertEquals(array($appender, $appenderSecond), $logger->getAppenders());
        $logger->addAppender($appenderSecond);
        $this->assertEquals(array($appender, $appenderSecond), $logger->getAppenders());

        $logger->removeAppender($appenderSecond);
        $this->assertEquals(array($appender), $logger->getAppenders());
        $logger->removeAppender($appender);
        $this->assertEquals(array(), $logger->getAppenders());
    }

    public function testAddictive()
    {
        $root = new LoggerTestMock('root');
        $logger = new Logger('logger', $root);
        $this->assertTrue($logger->getAddictive());
        $this->assertEquals($root, $logger->getParent());
        $logger->log(1, 'test1', $exFirst = new Exception());
        $this->assertEquals(array(array(1, 'test1', $exFirst)), $root->logs);

        $logger->setAddictive(false);
        $this->assertFalse($logger->getAddictive());
        $logger->log(2, 'test2', $exSecond = new Exception());
        $this->assertEquals(array(array(1, 'test1', $exFirst)), $root->logs);
    }

    public function testLogLevel()
    {
        $root = new LoggerTestMock('root');
        $logger = new Logger('logger', $root);
        $logger->trace('trace');
        $logger->debug('debug');
        $logger->info('info');
        $logger->warn('warn');
        $logger->error('error');
        $logger->fatal('fatal');

        $this->assertEquals(array(
            array(Logger::TRACE, 'trace', null),
            array(Logger::DEBUG, 'debug', null),
            array(Logger::INFO, 'info', null),
            array(Logger::WARN, 'warn', null),
            array(Logger::ERROR, 'error', null),
            array(Logger::FATAL, 'fatal', null),
        ), $root->logs);
    }

    public function levelProvider()
    {
        return array(
            array(Logger::OFF,  'OFF'),
            array(Logger::FATAL,'FATAL'),
            array(Logger::ERROR,'ERROR'),
            array(Logger::WARN, 'WARN'),
            array(Logger::INFO, 'INFO'),
            array(Logger::DEBUG,'DEBUG'),
            array(Logger::TRACE,'TRACE'),
            array(Logger::ALL,  'ALL'),
        );
    }

    /**
     * @param $level
     * @param $expected
     * @dataProvider levelProvider
     */
    public function testGetLevelName($level, $expected)
    {
        $this->assertEquals(
            $expected,
            Logger::getLevelName($level)
        );
    }
}


class LoggerTestMock extends Logger
{
    public $logs = array();

    public function log($level, $message, Exception $throwable = null)
    {
        $this->logs[] = func_get_args();
    }
}