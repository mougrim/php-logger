<?php

class LoggerAppenderStreamTest extends BaseLoggerTestCase
{
    protected $backupGlobals = true;

    public function testConstructIOException()
    {
        $this->setExpectedException('LoggerIOException');
        new LoggerAppenderStream('invalid://wefwef');
    }

    public function testNotUseLock()
    {
        $this->mockFunction('flock', '', 'throw new BadFunctionCallException();');
        $appender = new LoggerAppenderStream('php://stdout');
        $appender->setUseLock(false);
        $appender->write(Logger::INFO, '');
    }

    public function testNotUseLockShortMessage()
    {
        $this->mockFunction('flock', '', 'throw new BadFunctionCallException();');
        $appender = new LoggerAppenderStream('php://stdout');
        $appender->setUseLock(true);
        $appender->setUseLockShortMessage(false);
        $appender->write(Logger::INFO, '');
        $appender->write(Logger::INFO, str_pad('', 4096, '1'));
    }

    public function testUseLockShortMessage()
    {
        $GLOBALS['called']=false;
        $this->mockFunction('flock', '', '$GLOBALS["called"]=true; return true;');
        $appender = new LoggerAppenderStream('/tmp/log.txt');
        $appender->setUseLock(true);
        $appender->setUseLockShortMessage(false);
        $appender->write(Logger::INFO, str_pad('', 4097, '1'));
        $this->assertEquals(true, $GLOBALS['called']);
    }
}
