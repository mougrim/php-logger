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
        $GLOBALS['say']=null;
        $this->mockFunction(
            'system',
            function ($c) {
                $GLOBALS['say'] = $c;
                return '';
            }
        );
        $appender = new AppenderSay();
        $return = $appender->write(1, 'fuck, web site is down!');
        $this->assertTrue($return !== false);
        $this->assertEquals('say \'fuck, web site is down!\'', $GLOBALS['say']);
    }
}
