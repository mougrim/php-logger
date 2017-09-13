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
        $writeCalls = [];
        $this->redefineFunction(
            'fwrite',
            function ($handle, $string, $length = null) use (&$writeCalls) {
                $writeCalls[] = func_get_args();

                return true;
            }
        );

        $appender = new AppenderStd();
        $appender->write(Logger::INFO, 'test');
        $this->assertSame([[STDOUT, 'test']], $writeCalls);
    }

    public function testWriteStdout()
    {
        $writeCalls = [];
        $this->redefineFunction(
            'fwrite',
            function ($handle, $string, $length = null) use (&$writeCalls) {
                $writeCalls[] = func_get_args();

                return true;
            }
        );

        $appender = new AppenderStd();
        $appender->setStream('STDOUT');
        $appender->write(Logger::INFO, 'test');
        $this->assertSame([[STDOUT, 'test']], $writeCalls);
    }

    public function testWriteStderr()
    {
        $writeCalls = [];
        $this->redefineFunction(
            'fwrite',
            function ($handle, $string, $length = null) use (&$writeCalls) {
                $writeCalls[] = func_get_args();

                return true;
            }
        );

        $appender = new AppenderStd();
        $appender->setStream('STDERR');
        $appender->write(Logger::INFO, 'test');

        $this->assertSame([[STDERR, 'test']], $writeCalls);
    }

    public function testInvalidStream()
    {
        $this->setExpectedException(LoggerConfigurationException::class);
        $appender = new AppenderStd();
        $appender->setStream('ERROR');
    }
}
