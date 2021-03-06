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
        if (is_file($this->logFile)) {
            unlink($this->logFile);
        }
        parent::setUp();
    }

    protected function tearDown()
    {
        if (is_file($this->logFile)) {
            unlink($this->logFile);
        }
        parent::tearDown();
    }

    public function testConstructIOException()
    {
        $this->setExpectedException(LoggerIOException::class);
        new AppenderStream('invalid://wefwef');
    }

    public function testNotUseLock()
    {
        $callsQty = 0;
        $this->redefineFunction(
            'flock',
            function () use (&$callsQty) {
                ++$callsQty;

                return true;
            }
        );
        $appender = new AppenderStream($this->logFile);
        $appender->setUseLock(false);
        $appender->write(Logger::INFO, 'ok');
        $appender->write(Logger::INFO, str_pad('', 4096, '1'));
        $appender->write(Logger::INFO, str_pad('', 4097, '1'));
        $this->assertSame(0, $callsQty);
    }

    public function testNotUseLockShortMessage()
    {
        $callsQty = 0;
        $this->redefineFunction(
            'flock',
            function () use (&$callsQty) {
                ++$callsQty;

                return true;
            }
        );
        $appender = new AppenderStream($this->logFile);
        $appender->setUseLock(true);
        $appender->setUseLockShortMessage(false);
        $appender->write(Logger::INFO, '');
        $appender->write(Logger::INFO, str_pad('', 4096, '1'));
        $this->assertSame(0, $callsQty);
        $appender->write(Logger::INFO, str_pad('', 4097, '1'));
        $this->assertSame(0, $callsQty);
    }

    public function testUseLockShortMessage()
    {
        $callsQty = 0;
        $this->redefineFunction(
            'flock',
            function () use (&$callsQty) {
                ++$callsQty;

                return true;
            }
        );
        $appender = new AppenderStream($this->logFile);
        $appender->setUseLock(true);
        $appender->setUseLockShortMessage(true);
        $appender->write(Logger::INFO, str_pad('', 4096, '1'));
        $this->assertSame(2, $callsQty);
        $appender->write(Logger::INFO, str_pad('', 4097, '1'));
        $this->assertSame(4, $callsQty);
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
        if ($pid === -1) {
            $this->markTestIncomplete('could not fork');
        } elseif ($pid) {
        } else {
            $writer->write(1, $firstChild);
            die();
        }
        pcntl_waitpid($pid, $status);
        // second fork
        $pid = pcntl_fork();
        static::assertNotSame(-1, $pid, 'could not fork');
        if (!$pid) {
            $writer->write(1, $secondChild);
            die();
        }
        pcntl_waitpid($pid, $status);
        $writer->write(1, $after);

        $expected = $before.$firstChild.$secondChild.$after;
        $this->assertSame($expected, file_get_contents($this->logFile));
    }

    public function testReopen()
    {
        $appender = new AppenderStream($this->logFile);
        $appender->write(Logger::INFO, $first = uniqid('', true));
        $this->assertSame($first, file_get_contents($this->logFile));
        unlink($this->logFile);
        $appender->reopen();
        $appender->write(Logger::INFO, $second = uniqid('', true));
        $this->assertSame($second, file_get_contents($this->logFile));
    }

    public function testReopenForkClose()
    {
        $appender = new AppenderStream($this->logFile);

        $pid = pcntl_fork();
        static::assertNotSame(-1, $pid, 'could not fork');
        if (!$pid) {
            $appender->close();
            die();
        }
        pcntl_waitpid($pid, $status);

        $appender->write(Logger::INFO, $second = uniqid('', true));
        $appender->close();
        $this->assertSame($second, file_get_contents($this->logFile));
    }
}
