<?php

class LoggerAppenderSyslogTest extends BaseLoggerTestCase
{
    protected $backupGlobals=true;

    public function testWriteSyslog()
    {
        $GLOBALS['syslog']=array();
        $appender = new LoggerAppenderSyslog('id', LOG_PID, 0);
        $this->mockFunction('openlog', '', '$GLOBALS["syslog"][]="openlog";return true;');
        $this->mockFunction('syslog', '$priority, $message', '$GLOBALS["syslog"][]="syslog";$GLOBALS["syslog"][]=$priority;$GLOBALS["syslog"][]=$message;');
        $this->mockFunction('closelog', '', '$GLOBALS["syslog"][]="closelog";');
        $appender->write(Logger::INFO, 'test syslog');
        $this->assertEquals(array(
            'openlog',
            'syslog',
            LOG_INFO,
            'test syslog',
            'closelog'
        ), $GLOBALS['syslog']);
    }

    public function testErrorOpenSyslog()
    {
        $this->setExpectedException('LoggerException');
        $this->mockFunction('openlog', '', 'return false;');

        $appender = new LoggerAppenderSyslog('id', LOG_PID, 0);
        $appender->write(Logger::INFO, 'test syslog');
    }

    public function optionsProvider()
    {
        return array(
            array(LOG_PID, 'LOG_PID'),
            array(LOG_PID, LOG_PID),
            array(LOG_PID, array(LOG_PID)),
            array(LOG_PID|LOG_CONS, 'LOG_PID|LOG_CONS'),
            array(LOG_PID|LOG_CONS, LOG_PID|LOG_CONS),
            array(LOG_PID|LOG_CONS, array(LOG_PID,LOG_CONS)),
        );
    }

    /**
     * @param $expected
     * @param $options
     * @dataProvider optionsProvider
     */
    public function testParseOptions($expected, $options)
    {
        $this->assertEquals($expected,
            LoggerAppenderSyslog::parseOptions($options)
        );
    }

    public function testErrorInvalidOption()
    {
        $this->setExpectedException('LoggerException');
        LoggerAppenderSyslog::parseOptions('array');
    }

    public function testErrorBadType()
    {
        $this->setExpectedException('LoggerException');
        LoggerAppenderSyslog::parseOptions('STDOUT');
    }

    public function testErrorBadArgument()
    {
        $this->setExpectedException('LoggerException');
        LoggerAppenderSyslog::parseOptions(STDOUT);
    }

    public function syslogPriorityProvider()
    {
        return array(
            array(LOG_ALERT, Logger::OFF),
            array(LOG_ALERT, Logger::FATAL),
            array(LOG_ERR, Logger::ERROR),
            array(LOG_WARNING, Logger::WARN),
            array(LOG_INFO, Logger::INFO),
            array(LOG_DEBUG, Logger::DEBUG),
            array(LOG_DEBUG, Logger::TRACE),
            array(LOG_DEBUG, Logger::ALL),
        );
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
            LoggerAppenderSyslog::getSyslogPriority($level)
        );
    }
}
