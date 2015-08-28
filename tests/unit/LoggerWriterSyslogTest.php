<?php
namespace Mougrim\Logger;

use Mougrim\Logger\Appender\AppenderSyslog;

class LoggerWriterSyslogTest extends BaseLoggerTestCase
{
    public function syslogProvider()
    {
        return [
            'Level off' => [
                'expected' => LOG_ALERT,
                'actual' => Logger::OFF,
            ],
            'Level less off' => [
                'expected' => LOG_ALERT,
                'actual' => Logger::OFF - 1,
            ],
            'Level greater fatal' => [
                'expected' => LOG_ALERT,
                'actual' => Logger::FATAL + 1,
            ],
            'Level fatal' => [
                'expected' => LOG_ALERT,
                'actual' => Logger::FATAL,
            ],
            'Level less fatal' => [
                'expected' => LOG_ERR,
                'actual' => Logger::FATAL - 1,
            ],
            'Level greater error' => [
                'expected' => LOG_ERR,
                'actual' => Logger::ERROR + 1,
            ],
            'Level error' => [
                'expected' => LOG_ERR,
                'actual' => Logger::ERROR,
            ],
            'Level less error' => [
                'expected' => LOG_WARNING,
                'actual' => Logger::ERROR - 1,
            ],
            'Level greater warn' => [
                'expected' => LOG_WARNING,
                'actual' => Logger::WARN + 1,
            ],
            'Level warn' => [
                'expected' => LOG_WARNING,
                'actual' => Logger::WARN,
            ],
            'Level less warn' => [
                'expected' => LOG_INFO,
                'actual' => Logger::WARN - 1,
            ],
            'Level greater info' => [
                'expected' => LOG_INFO,
                'actual' => Logger::INFO + 1,
            ],
            'Level info' => [
                'expected' => LOG_INFO,
                'actual' => Logger::INFO,
            ],
            'Level less info' => [
                'expected' => LOG_DEBUG,
                'actual' => Logger::INFO - 1,
            ],
            'Level greater debug' => [
                'expected' => LOG_DEBUG,
                'actual' => Logger::DEBUG + 1,
            ],
            'Level debug' => [
                'expected' => LOG_DEBUG,
                'actual' => Logger::DEBUG,
            ],
            'Level less debug' => [
                'expected' => LOG_DEBUG,
                'actual' => Logger::DEBUG - 1,
            ],
            'Level greater trace' => [
                'expected' => LOG_DEBUG,
                'actual' => Logger::TRACE + 1,
            ],
            'Level trace' => [
                'expected' => LOG_DEBUG,
                'actual' => Logger::TRACE,
            ],
            'Level less trace' => [
                'expected' => LOG_DEBUG,
                'actual' => Logger::TRACE - 1,
            ],
            'Level all' => [
                'expected' => LOG_DEBUG,
                'actual' => Logger::ALL,
            ],
        ];
    }

    /**
     * @param int $expected
     * @param int $actual
     * @dataProvider syslogProvider
     */
    public function testGetSyslogPriority($expected, $actual)
    {
        $this->assertEquals($expected, AppenderSyslog::getSyslogPriority($actual));
    }
}
