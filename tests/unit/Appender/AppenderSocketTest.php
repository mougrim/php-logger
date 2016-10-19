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
        $GLOBALS["socket"] = [];
        $this->mockFunction(
            'fsockopen',
            function($host, $port, &$errorCode, &$errorMessage, $delay) {
                $GLOBALS['socket'][] = func_get_args();
                return 'SocketMock';
            }
        );
        $this->mockFunction(
            'fwrite',
            function() {
                $GLOBALS['socket'][] = func_get_args();
                return true;
            }
        );
        $this->mockFunction(
            'fclose',
            function() {
                $GLOBALS['socket'][] = func_get_args();
                return true;
            }
        );
        $appender = new AppenderSocket('8.8.8.8', 80, 10);
        $appender->write(Logger::INFO, 'test');
        $this->assertEquals([
            ['8.8.8.8', 80, null, null, 10],
            ["SocketMock", 'test'],
            ['SocketMock']
        ], $GLOBALS["socket"]);
    }
}
