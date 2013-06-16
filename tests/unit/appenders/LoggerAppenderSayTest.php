<?php

/**
 * Special for mac cool appender!
 */
class LoggerAppenderSayTest extends BaseLoggerTestCase
{
    protected $backupGlobals = true;

    public function testWrite()
    {
        $GLOBALS['say']=null;
        $this->mockFunction('system', '$c', '$GLOBALS["say"]=$c;return "";');
        $appender = new LoggerAppenderSay();
        $return = $appender->write(1, 'fuck, web site is down!');
        $this->assertTrue($return !== false);
        $this->assertEquals('say \'fuck, web site is down!\'', $GLOBALS['say']);
    }
}