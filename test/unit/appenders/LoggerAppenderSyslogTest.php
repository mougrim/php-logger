<?php

class LoggerAppenderSyslogTest extends PHPUnit_Framework_TestCase
{
    public function testWriteSyslog()
    {
        $appender = new LoggerAppenderSyslog('id', LOG_PID, 0);
        $appender->write(Logger::INFO, 'test syslog');
    }

    public function testErrorOpenSyslog()
    {
        $this->setExpectedException('LoggerException');
        $error=null;
        if(extension_loaded('runkit.so')){
            $this->markTestIncomplete('no runkit');
        }
        ini_set('runkit.internal_override', '1');
        runkit_function_copy('openlog', 'openlog_copy');
        runkit_function_redefine('openlog', '', 'return false;');
        try{
            $appender = new LoggerAppenderSyslog('id', LOG_PID, 0);
            $appender->write(Logger::INFO, 'test syslog');
        }catch (Exception $e){
            $error=$e;
        }
        runkit_function_remove('openlog');
        runkit_function_rename('openlog_copy', 'openlog');
        if($error){
            throw $error;
        }
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
