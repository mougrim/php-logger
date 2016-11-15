<?php

namespace Mougrim\Logger\Appender;

use Mougrim\Logger\BaseLoggerTestCase;

/**
 * Special for mac cool appender!
 */
class AppenderSayTest extends BaseLoggerTestCase
{
    protected $backupGlobals = true;

    public function testWrite()
    {
        $commands = [];
        $this->mockFunction(
            'system',
            function ($command) use (&$commands) {
                $commands[] = $command;

                return '';
            }
        );
        $appender = new AppenderSay();
        $return = $appender->write(1, 'fuck, web site is down!');
        $this->assertNotFalse($return);
        $this->assertSame(["say 'fuck, web site is down!'"], $commands);
    }
}
