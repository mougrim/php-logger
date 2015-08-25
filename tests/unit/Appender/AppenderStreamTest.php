<?php
namespace Mougrim\Logger\Appender;

use Mougrim\Logger\BaseLoggerTestCase;
use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerIOException;

class AppenderStreamTest extends BaseLoggerTestCase
{
    protected $backupGlobals = true;
    private $logFile = '/tmp/log.txt';

    protected function setUp()
    {
        if (is_file($this->logFile)) unlink($this->logFile);
        parent::setUp();
    }

    protected function tearDown()
    {
        if (is_file($this->logFile)) unlink($this->logFile);
        parent::tearDown();
    }

    public function testConstructIOException()
    {
        $this->setExpectedException(LoggerIOException::class);
        new AppenderStream('invalid://wefwef');
    }

    public function testNotUseLock()
    {
        $GLOBALS['called']=false;
        $this->mockFunction('flock', '', '$GLOBALS["called"]=true; return true;');
        $appender = new AppenderStream($this->logFile);
        $appender->setUseLock(false);
        $appender->write(Logger::INFO, 'ok');
        $appender->write(Logger::INFO, str_pad('', 4096, '1'));
        $appender->write(Logger::INFO, str_pad('', 4097, '1'));
        $this->assertEquals(false, $GLOBALS['called']);
    }

    public function testNotUseLockShortMessage()
    {
        $GLOBALS['called']=false;
        $this->mockFunction('flock', '', '$GLOBALS["called"]=true; return true;');
        $appender = new AppenderStream($this->logFile);
        $appender->setUseLock(true);
        $appender->setUseLockShortMessage(false);
        $appender->write(Logger::INFO, '');
        $appender->write(Logger::INFO, str_pad('', 4096, '1'));
        $this->assertEquals(false, $GLOBALS['called']);
    }

    public function testUseLockShortMessage()
    {
        $GLOBALS['called'] = false;
        $this->mockFunction('flock', '', '$GLOBALS["called"]=true; return true;');
        $appender = new AppenderStream($this->logFile);
        $appender->setUseLock(true);
        $appender->setUseLockShortMessage(false);
        $appender->write(Logger::INFO, str_pad('', 4097, '1'));
        $this->assertEquals(true, $GLOBALS['called']);
    }

    public function testFork()
    {

        $before = uniqid('before');
        $firstChild = uniqid('firstChild');
        $secondChild = uniqid('secondChild');
        $after = uniqid('after');

        $writer = new AppenderStream($this->logFile);
        $writer->write(1, $before);
        // first fork
        $pid = pcntl_fork();
        if ($pid == -1) {
            $this->markTestIncomplete('could not fork');
        } else if ($pid) {

        } else {
            $writer->write(1, $firstChild);
            die();
        }
        pcntl_waitpid($pid, $status);
        sleep(1);
        // second fork
        $pid = pcntl_fork();
        if ($pid == -1) {
            $this->markTestIncomplete('could not fork');
        } else if ($pid) {

        } else {
            $writer->write(1, $secondChild);
            die();
        }
        pcntl_waitpid($pid, $status);
        sleep(1);
        $writer->write(1, $after);

        $expected = $before . $firstChild . $secondChild . $after;
        $this->assertEquals($expected, file_get_contents($this->logFile));
    }

    public function testReopen()
    {
        $appender = new AppenderStream($this->logFile);
        $appender->write(Logger::INFO, $first = uniqid('', true));
        $this->assertEquals($first, file_get_contents($this->logFile));
        unlink($this->logFile);
        $appender->reopen();
        $appender->write(Logger::INFO, $second = uniqid('', true));
        $this->assertEquals($second, file_get_contents($this->logFile));
    }

    public function testReopenForkClose()
    {
        $appender = new AppenderStream($this->logFile);

        $pid = pcntl_fork();
        if ($pid == -1) {
            $this->markTestIncomplete('could not fork');
        } else if ($pid) {
        } else {
            $appender->close();
            die();
        }
        pcntl_waitpid($pid, $status);
        sleep(1);

        $appender->write(Logger::INFO, $second = uniqid('', true));
        $appender->close();
        $this->assertEquals($second, file_get_contents($this->logFile));
    }
}
