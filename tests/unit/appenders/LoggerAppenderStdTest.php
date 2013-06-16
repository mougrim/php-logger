<?php

class LoggerAppenderStdTest extends BaseLoggerTestCase
{
    protected $backupGlobals = true;

    public function testWriteDefault()
    {
        $error = null;
        $GLOBALS['stream'] = null;
        $this->mockFunction('fwrite', '$a', '$GLOBALS["stream"]=$a;');

        $appender = new LoggerAppenderStd();
        $appender->write(Logger::INFO, 'test');
        $this->assertTrue(STDOUT === $GLOBALS['stream']);
    }

    public function testWriteStdout()
    {
        $GLOBALS['stream'] = null;
        $this->mockFunction('fwrite', '$a', '$GLOBALS["stream"]=$a;');

        $appender = new LoggerAppenderStd();
        $appender->setStream('STDOUT');
        $appender->write(Logger::INFO, 'test');
        $this->assertTrue(STDOUT === $GLOBALS['stream']);
    }

    public function testWriteStderr()
    {
        $GLOBALS['stream'] = null;
        $this->mockFunction('fwrite', '$a', '$GLOBALS["stream"]=$a;');

        $appender = new LoggerAppenderStd();
        $appender->setStream('STDERR');
        $appender->write(Logger::INFO, 'test');

        $this->assertTrue(STDERR === $GLOBALS['stream']);
    }

    public function testInvalidStream()
    {
        $this->setExpectedException('LoggerIOException');
        $appender = new LoggerAppenderStd();
        $appender->setStream('ERROR');
    }
}
