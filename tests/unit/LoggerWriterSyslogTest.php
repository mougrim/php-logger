<?php
namespace Mougrim\Logger;

use Mougrim\Logger\Appender\AppenderSyslog;

class LoggerWriterSyslogTest extends BaseLoggerTestCase
{
    public function syslogProvider()
    {
        return [
            [LOG_ALERT, Logger::OFF],
            [LOG_ALERT, Logger::OFF - 1],
            [LOG_ALERT, Logger::FATAL],
            [LOG_ALERT, Logger::FATAL + 1],
            [LOG_ERR, Logger::FATAL - 1],
            [LOG_ERR, Logger::ERROR + 1],
            [LOG_ERR, Logger::ERROR],
            [LOG_WARNING, Logger::ERROR - 1],
            [LOG_WARNING, Logger::WARN + 1],
            [LOG_WARNING, Logger::WARN],
            [LOG_INFO, Logger::WARN - 1],
            [LOG_INFO, Logger::INFO + 1],
            [LOG_INFO, Logger::INFO],
            [LOG_DEBUG, Logger::INFO - 1],
            [LOG_DEBUG, Logger::DEBUG + 1],
            [LOG_DEBUG, Logger::DEBUG],
            [LOG_DEBUG, Logger::TRACE + 1],
            [LOG_DEBUG, Logger::TRACE],
            [LOG_DEBUG, Logger::TRACE - 1],
            [LOG_DEBUG, Logger::ALL],
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
