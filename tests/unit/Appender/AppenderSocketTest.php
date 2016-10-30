<?php
namespace Mougrim\Logger\Appender;

use Mougrim\Logger\BaseLoggerTestCase;
use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerIOException;

class AppenderSocketTest extends BaseLoggerTestCase
{
    protected $backupGlobals = true;

    public function testCouldNotOpenSocket()
    {
        $this->setExpectedException(LoggerIOException::class);
        $this->mockFunction('fsockopen', function($host, $port, &$errorCode, &$errorMessage, $delay) {return false;});
        $appender = new AppenderSocket('8.8.8.8', 80);
        $appender->write(Logger::INFO, 'test');
    }

    public function testErrorWrite()
    {
        $this->setExpectedException(LoggerIOException::class);
        $this->mockFunction('fsockopen', function($host, $port, &$errorCode, &$errorMessage, $delay) {return true;});
        $this->mockFunction('fwrite', function() {return false;});
        $this->mockFunction('fclose', function() {return false;});
        $appender = new AppenderSocket('8.8.8.8', 80, 10);
        $appender->write(Logger::INFO, 'test');
    }

    public function testWrite()
    {
        $sockOpenCalls = [];
        $this->mockFunction(
            'fsockopen',
            function($host, $port, &$errorCode, &$errorMessage, $delay) use (&$sockOpenCalls) {
                $sockOpenCalls[] = func_get_args();
                return 'SocketMock';
            }
        );
        $writeCalls = [];
        $this->mockFunction(
            'fwrite',
            function() use (&$writeCalls) {
                $writeCalls[] = func_get_args();
                return true;
            }
        );
        $closeCalls = [];
        $this->mockFunction(
            'fclose',
            function() use (&$closeCalls) {
                $closeCalls[] = func_get_args();
                return true;
            }
        );
        $appender = new AppenderSocket('8.8.8.8', 80, 10);
        $appender->write(Logger::INFO, 'test');
        $this->assertSame([['8.8.8.8', 80, null, null, 10]], $sockOpenCalls);
        $this->assertSame([['SocketMock', 'test']], $writeCalls);
        $this->assertSame([['SocketMock']], $closeCalls);
    }
}
