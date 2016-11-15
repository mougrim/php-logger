<?php

namespace Mougrim\Logger\Appender;

use Mougrim\Logger\BaseLoggerTestCase;
use Mougrim\Logger\Logger;

class AppenderStreamBufferTest extends BaseLoggerTestCase
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

    public function testThreshold()
    {
        $messages = [];
        /** @noinspection PhpUnusedParameterInspection */
        $this->mockFunction(
            'fwrite',
            function ($handle, $string) use (&$messages) {
                $messages[] = $string;

                return true;
            }
        );

        $appender = new AppenderStreamBuffer('php://stdout');
        $appender->setThreshold(Logger::ERROR);

        $logger = new Logger('');

        $appender->append($logger, Logger::ALL, 'ALL');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::TRACE, 'TRACE');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::DEBUG, 'DEBUG');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::INFO, 'INFO');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::WARN, 'WARN');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::ERROR, 'ERROR');
        $this->assertSame(
            [
                'ALL',
                'TRACE',
                'DEBUG',
                'INFO',
                'WARN',
                'ERROR',
            ],
            $messages
        );

        $appender->append($logger, Logger::FATAL, 'FATAL');
        $this->assertSame(
            [
                'ALL',
                'TRACE',
                'DEBUG',
                'INFO',
                'WARN',
                'ERROR',
                'FATAL',
            ],
            $messages
        );

        $appender->append($logger, Logger::OFF, 'OFF');
        $this->assertSame(
            [
                'ALL',
                'TRACE',
                'DEBUG',
                'INFO',
                'WARN',
                'ERROR',
                'FATAL',
                'OFF',
            ],
            $messages
        );
    }

    public function testThresholdMin()
    {
        $messages = [];
        /** @noinspection PhpUnusedParameterInspection */
        $this->mockFunction(
            'fwrite',
            function ($handle, $string) use (&$messages) {
                $messages[] = $string;

                return true;
            }
        );

        $appender = new AppenderStreamBuffer('php://stdout');
        $appender->setThreshold(Logger::ERROR);
        $appender->setMinLevel(Logger::DEBUG);

        $logger = new Logger('');

        $appender->append($logger, Logger::ALL, 'ALL');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::TRACE, 'TRACE');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::DEBUG, 'DEBUG');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::INFO, 'INFO');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::WARN, 'WARN');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::ERROR, 'ERROR');
        $this->assertSame(
            [
                'DEBUG',
                'INFO',
                'WARN',
                'ERROR',
            ],
            $messages
        );

        $appender->append($logger, Logger::FATAL, 'FATAL');
        $this->assertSame(
            [
                'DEBUG',
                'INFO',
                'WARN',
                'ERROR',
                'FATAL',
            ],
            $messages
        );

        $appender->append($logger, Logger::OFF, 'OFF');
        $this->assertSame(
            [
                'DEBUG',
                'INFO',
                'WARN',
                'ERROR',
                'FATAL',
                'OFF',
            ],
            $messages
        );
    }

    public function testThresholdMax()
    {
        $messages = [];
        /** @noinspection PhpUnusedParameterInspection */
        $this->mockFunction(
            'fwrite',
            function ($handle, $string) use (&$messages) {
                $messages[] = $string;

                return true;
            }
        );

        $appender = new AppenderStreamBuffer('php://stdout');
        $appender->setThreshold(Logger::ERROR);
        $appender->setMaxLevel(Logger::FATAL);

        $logger = new Logger('');

        $appender->append($logger, Logger::ALL, 'ALL');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::TRACE, 'TRACE');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::DEBUG, 'DEBUG');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::INFO, 'INFO');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::WARN, 'WARN');
        $this->assertSame([], $messages);

        $appender->append($logger, Logger::ERROR, 'ERROR');
        $this->assertSame(
            [
                'ALL',
                'TRACE',
                'DEBUG',
                'INFO',
                'WARN',
                'ERROR',
            ],
            $messages
        );

        $appender->append($logger, Logger::FATAL, 'FATAL');
        $this->assertSame(
            [
                'ALL',
                'TRACE',
                'DEBUG',
                'INFO',
                'WARN',
                'ERROR',
                'FATAL',
            ],
            $messages
        );

        $appender->append($logger, Logger::OFF, 'OFF');
        $this->assertSame(
            [
                'ALL',
                'TRACE',
                'DEBUG',
                'INFO',
                'WARN',
                'ERROR',
                'FATAL',
            ],
            $messages
        );
    }
}
