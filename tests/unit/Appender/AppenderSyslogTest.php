<?php
namespace Mougrim\Logger\Appender;

use Mougrim\Logger\BaseLoggerTestCase;
use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerException;

class AppenderSyslogTest extends BaseLoggerTestCase
{
    protected $backupGlobals = true;

    public function testWriteSyslog()
    {
        $openLogCalls = [];
        $this->mockFunction(
            'openlog',
            function() use (&$openLogCalls) {
                $openLogCalls[] = func_get_args();
                return true;
            }
        );
        $sysLogCalls = [];
        $this->mockFunction(
            'syslog',
            function($priority, $message) use (&$sysLogCalls) {
                $sysLogCalls[] = func_get_args();
            }
        );
        $closeLogCalls = [];
        $this->mockFunction('closelog', function() use (&$closeLogCalls) {$closeLogCalls[] = func_get_args();});
        $appender = new AppenderSyslog('id', LOG_PID, 0);
        $appender->write(Logger::INFO, 'test syslog');
        $this->assertSame([['id', LOG_PID, 0]], $openLogCalls);
        $this->assertSame([[LOG_INFO, 'test syslog']], $sysLogCalls);
        $this->assertSame([[]], $closeLogCalls);
    }

    public function testErrorOpenSyslog()
    {
        $this->setExpectedException(LoggerException::class);
        $this->mockFunction('openlog', function () {return false;});

        $appender = new AppenderSyslog('id', LOG_PID, 0);
        $appender->write(Logger::INFO, 'test syslog');
    }

    public function optionsProvider()
    {
        return [
            'String option' => [
                'expected' => LOG_PID,
                'options' => 'LOG_PID',
            ],
            'One bitwise option' => [
                'expected' => LOG_PID,
                'options' => LOG_PID,
            ],
            'Array of one bitwise option' => [
                'expected' => LOG_PID,
                'options' => [LOG_PID],
            ],
            'Two bitwise string options' => [
                'expected' => LOG_PID | LOG_CONS,
                'options' => 'LOG_PID|LOG_CONS',
            ],
            'Two bitwise options' => [
                'expected' => LOG_PID | LOG_CONS,
                'options' => LOG_PID | LOG_CONS,
            ],
            'Array of two bitwise options' => [
                'expected' => LOG_PID | LOG_CONS,
                'options' => [LOG_PID, LOG_CONS],
            ],
        ];
    }

    /**
     * @param $expected
     * @param $options
     * @dataProvider optionsProvider
     */
    public function testParseOptions($expected, $options)
    {
        $this->assertEquals($expected,
            AppenderSyslog::parseOptions($options)
        );
    }

    public function testErrorInvalidOption()
    {
        $this->setExpectedException(LoggerException::class);
        AppenderSyslog::parseOptions('array');
    }

    public function testErrorBadType()
    {
        $this->setExpectedException(LoggerException::class);
        AppenderSyslog::parseOptions('STDOUT');
    }

    public function testErrorBadArgument()
    {
        $this->setExpectedException(LoggerException::class);
        AppenderSyslog::parseOptions(STDOUT);
    }

    public function syslogPriorityProvider()
    {
        return [
            'Level off' => [
                'expected' => LOG_ALERT,
                'level' => Logger::OFF,
            ],
            'Level less off' => [
                'expected' => LOG_ALERT,
                'level' => Logger::OFF - 1,
            ],
            'Level greater fatal' => [
                'expected' => LOG_ALERT,
                'level' => Logger::FATAL + 1,
            ],
            'Level fatal' => [
                'expected' => LOG_ALERT,
                'level' => Logger::FATAL,
            ],
            'Level less fatal' => [
                'expected' => LOG_ERR,
                'level' => Logger::FATAL - 1,
            ],
            'Level greater error' => [
                'expected' => LOG_ERR,
                'level' => Logger::ERROR + 1,
            ],
            'Level error' => [
                'expected' => LOG_ERR,
                'level' => Logger::ERROR,
            ],
            'Level less error' => [
                'expected' => LOG_WARNING,
                'level' => Logger::ERROR - 1,
            ],
            'Level greater warn' => [
                'expected' => LOG_WARNING,
                'level' => Logger::WARN + 1,
            ],
            'Level warn' => [
                'expected' => LOG_WARNING,
                'level' => Logger::WARN,
            ],
            'Level less warn' => [
                'expected' => LOG_INFO,
                'level' => Logger::WARN - 1,
            ],
            'Level greater info' => [
                'expected' => LOG_INFO,
                'level' => Logger::INFO + 1,
            ],
            'Level info' => [
                'expected' => LOG_INFO,
                'level' => Logger::INFO,
            ],
            'Level less info' => [
                'expected' => LOG_DEBUG,
                'level' => Logger::INFO - 1,
            ],
            'Level greater debug' => [
                'expected' => LOG_DEBUG,
                'level' => Logger::DEBUG + 1,
            ],
            'Level debug' => [
                'expected' => LOG_DEBUG,
                'level' => Logger::DEBUG,
            ],
            'Level less debug' => [
                'expected' => LOG_DEBUG,
                'level' => Logger::DEBUG - 1,
            ],
            'Level greater trace' => [
                'expected' => LOG_DEBUG,
                'level' => Logger::TRACE + 1,
            ],
            'Level trace' => [
                'expected' => LOG_DEBUG,
                'level' => Logger::TRACE,
            ],
            'Level less trace' => [
                'expected' => LOG_DEBUG,
                'level' => Logger::TRACE - 1,
            ],
            'Level all' => [
                'expected' => LOG_DEBUG,
                'level' => Logger::ALL,
            ],
        ];
    }

    /**
     * @param $expected
     * @param $level
     * @dataProvider syslogPriorityProvider
     */
    public function testSyslogPriority($expected, $level)
    {
        $this->assertEquals(
            $expected,
            AppenderSyslog::getSyslogPriority($level)
        );
    }
}
