<?php
namespace Mougrim\Logger\Appender;

use Mougrim\Logger\BaseLoggerTestCase;
use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerConfigurationException;

class AppenderStdTest extends BaseLoggerTestCase
{
    protected $backupGlobals = true;

    public function testWriteDefault()
    {
        $error = null;
        $GLOBALS['stream'] = null;
        $this->mockFunction('fwrite', '$a', '$GLOBALS["stream"]=$a;');

        $appender = new AppenderStd();
        $appender->write(Logger::INFO, 'test');
        $this->assertTrue(STDOUT === $GLOBALS['stream']);
    }

    public function testWriteStdout()
    {
        $GLOBALS['stream'] = null;
        $this->mockFunction('fwrite', '$a', '$GLOBALS["stream"]=$a;');

        $appender = new AppenderStd();
        $appender->setStream('STDOUT');
        $appender->write(Logger::INFO, 'test');
        $this->assertTrue(STDOUT === $GLOBALS['stream']);
    }

    public function testWriteStderr()
    {
        $GLOBALS['stream'] = null;
        $this->mockFunction('fwrite', '$a', '$GLOBALS["stream"]=$a;');

        $appender = new AppenderStd();
        $appender->setStream('STDERR');
        $appender->write(Logger::INFO, 'test');

        $this->assertTrue(STDERR === $GLOBALS['stream']);
    }

    public function testInvalidStream()
    {
        $this->setExpectedException(LoggerConfigurationException::class);
        $appender = new AppenderStd();
        $appender->setStream('ERROR');
    }
}
