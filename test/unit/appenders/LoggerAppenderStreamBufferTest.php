<?php

class LoggerAppenderStreamBufferTest extends BaseLoggerTestCase
{
    protected $backupGlobals = true;
    private $logFile = '/tmp/log.txt';

    protected function setUp()
    {
        if (is_file($this->logFile)) unlink($this->logFile);
        parent::setUp();
    }

    protected function tearDown()
    {
        if (is_file($this->logFile)) unlink($this->logFile);
        parent::tearDown();
    }

    public function testThreshold()
    {
        $GLOBALS['message'] = '';
        $this->mockFunction('fwrite', '$stream, $message', '$GLOBALS["message"].=$message;');

        $appender = new LoggerAppenderStreamBuffer('php://stdout');
        $appender->setThreshold(Logger::ERROR);

        $logger = new Logger('');

        $appender->append($logger, Logger::ALL, 'ALL');
        $this->assertEquals('', $GLOBALS['message']);

        $appender->append($logger, Logger::TRACE, 'TRACE');
        $this->assertEquals('', $GLOBALS['message']);

        $appender->append($logger, Logger::DEBUG, 'DEBUG');
        $this->assertEquals('', $GLOBALS['message']);

        $appender->append($logger, Logger::INFO, 'INFO');
        $this->assertEquals('', $GLOBALS['message']);

        $appender->append($logger, Logger::WARN, 'WARN');
        $this->assertEquals('', $GLOBALS['message']);

        $appender->append($logger, Logger::ERROR, 'ERROR');
        $this->assertEquals('ALLTRACEDEBUGINFOWARNERROR', $GLOBALS['message']);

        $appender->append($logger, Logger::FATAL, 'FATAL');
        $this->assertEquals('ALLTRACEDEBUGINFOWARNERRORFATAL', $GLOBALS['message']);

        $appender->append($logger, Logger::OFF, 'OFF');
        $this->assertEquals('ALLTRACEDEBUGINFOWARNERRORFATALOFF', $GLOBALS['message']);
    }
}
