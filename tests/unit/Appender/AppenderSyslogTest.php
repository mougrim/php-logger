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
        $GLOBALS['syslog'] = [];
        $appender = new AppenderSyslog('id', LOG_PID, 0);
        $this->mockFunction('openlog', '', '$GLOBALS["syslog"][]="openlog";return true;');
        $this->mockFunction('syslog', '$priority, $message', '$GLOBALS["syslog"][]="syslog";$GLOBALS["syslog"][]=$priority;$GLOBALS["syslog"][]=$message;');
        $this->mockFunction('closelog', '', '$GLOBALS["syslog"][]="closelog";');
        $appender->write(Logger::INFO, 'test syslog');
        $this->assertEquals([
            'openlog',
            'syslog',
            LOG_INFO,
            'test syslog',
            'closelog'
        ], $GLOBALS['syslog']);
    }

    public function testErrorOpenSyslog()
    {
        $this->setExpectedException(LoggerException::class);
        $this->mockFunction('openlog', '', 'return false;');

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
            'Level fatal' => [
                'expected' => LOG_ALERT,
                'level' => Logger::FATAL,
            ],
            'Level error' => [
                'expected' => LOG_ERR,
                'level' => Logger::ERROR,
            ],
            'Level warn' => [
                'expected' => LOG_WARNING,
                'level' => Logger::WARN,
            ],
            'Level info' => [
                'expected' => LOG_INFO,
                'level' => Logger::INFO,
            ],
            'Level debug' => [
                'expected' => LOG_DEBUG,
                'level' => Logger::DEBUG,
            ],
            'Level trace' => [
                'expected' => LOG_DEBUG,
                'level' => Logger::TRACE,
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
