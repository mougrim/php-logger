<?php
namespace Mougrim\Logger\Appender;

use Mougrim\Logger\BaseLoggerTestCase;
use Mougrim\Logger\Logger;

class AppenderAbstractTest extends BaseLoggerTestCase
{
    public function testMinLevel()
    {
        $logger = new Logger('root');

        $appender = new AppenderAbstractTestAppender();

        $appender->append($logger, Logger::INFO, '1');
        $this->assertEquals('1', $appender->data);

        $appender->setMinLevel(Logger::INFO);
        $appender->append($logger, Logger::INFO, '2');
        $appender->append($logger, Logger::DEBUG, '3');
        $this->assertEquals('12', $appender->data);
    }

    public function testMaxLevel()
    {
        $logger = new Logger('root');

        $appender = new AppenderAbstractTestAppender();

        $appender->append($logger, Logger::INFO, '1');
        $this->assertEquals('1', $appender->data);

        $appender->setMaxLevel(Logger::INFO);
        $appender->append($logger, Logger::INFO, '2');
        $appender->append($logger, Logger::FATAL, '3');
        $appender->append($logger, Logger::DEBUG, '4');
        $this->assertEquals('124', $appender->data);
    }
}


class AppenderAbstractTestAppender extends AppenderAbstract
{
    public $data = '';

    public function write($level, $message)
    {
        $this->data .= $message;
    }
}
