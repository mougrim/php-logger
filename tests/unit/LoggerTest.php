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
        $rootAppender = new LoggerAppenderTest();
        $root = new Logger('root');
        $root->addAppender($rootAppender);
        $logger = new Logger('logger', $root);
        $this->assertTrue($logger->getAddictive());
        $this->assertEquals($root, $logger->getParent());
        $logger->log(1, 'test1', $exFirst = new Exception());
        $this->assertEquals(array(array(1, 'test1')), $rootAppender->logs);

        $logger->setAddictive(false);
        $this->assertFalse($logger->getAddictive());
        $logger->log(2, 'test2', $exSecond = new Exception());
        $this->assertEquals(array(array(1, 'test1')), $rootAppender->logs);
    }

    public function testLogLevel()
    {
        $rootAppender = new LoggerAppenderTest();
        $root = new Logger('root');
        $root->addAppender($rootAppender);
        $logger = new Logger('logger', $root);
        $logger->trace('trace');
        $logger->debug('debug');
        $logger->info('info');
        $logger->warn('warn');
        $logger->error('error');
        $logger->fatal('fatal');

        $this->assertEquals(array(
            array(Logger::TRACE, 'trace'),
            array(Logger::DEBUG, 'debug'),
            array(Logger::INFO, 'info'),
            array(Logger::WARN, 'warn'),
            array(Logger::ERROR, 'error'),
            array(Logger::FATAL, 'fatal'),
        ), $rootAppender->logs);
    }

    public function levelProvider()
    {
        return array(
            array(Logger::OFF, 'OFF'),
            array(Logger::FATAL, 'FATAL'),
            array(Logger::ERROR, 'ERROR'),
            array(Logger::WARN, 'WARN'),
            array(Logger::INFO, 'INFO'),
            array(Logger::DEBUG, 'DEBUG'),
            array(Logger::TRACE, 'TRACE'),
            array(Logger::ALL, 'ALL'),
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

    /**
     * @param $expected
     * @param $levelName
     * @dataProvider levelProvider
     */
    public function testGetLevelByName($expected, $levelName)
    {
        $this->assertEquals(
            $expected,
            Logger::getLevelByName($levelName)
        );
    }
}

class LoggerAppenderTest extends \LoggerAppenderAbstract
{
    public $logs = array();

    public function write($level, $message)
    {
        $this->logs[] = func_get_args();
    }
}