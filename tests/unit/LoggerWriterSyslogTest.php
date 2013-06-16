<?php

class LoggerWriterSyslogTest extends PHPUnit_Framework_TestCase
{
    public function syslogProvider()
    {
        return array(
            array(LOG_ALERT, Logger::OFF),
            array(LOG_ALERT, Logger::OFF - 1),
            array(LOG_ALERT, Logger::FATAL),
            array(LOG_ALERT, Logger::FATAL + 1),
            array(LOG_ERR, Logger::FATAL - 1),
            array(LOG_ERR, Logger::ERROR + 1),
            array(LOG_ERR, Logger::ERROR),
            array(LOG_WARNING, Logger::ERROR - 1),
            array(LOG_WARNING, Logger::WARN + 1),
            array(LOG_WARNING, Logger::WARN),
            array(LOG_INFO, Logger::WARN - 1),
            array(LOG_INFO, Logger::INFO + 1),
            array(LOG_INFO, Logger::INFO),
            array(LOG_DEBUG, Logger::INFO - 1),
            array(LOG_DEBUG, Logger::DEBUG + 1),
            array(LOG_DEBUG, Logger::DEBUG),
            array(LOG_DEBUG, Logger::TRACE + 1),
            array(LOG_DEBUG, Logger::TRACE),
            array(LOG_DEBUG, Logger::TRACE - 1),
            array(LOG_DEBUG, Logger::ALL),
        );
    }

    /**
     * @param int $expected
     * @param int $actual
     * @dataProvider syslogProvider
     */
    public function testGetSyslogPriority($expected, $actual)
    {
        $this->assertEquals($expected, LoggerAppenderSyslog::getSyslogPriority($actual));
    }
}
