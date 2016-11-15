<?php

namespace Mougrim\Logger\Appender;

use Mougrim\Logger\LoggerConfigurationException;
use Mougrim\Logger\LoggerPolicy;

defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));
defined('STDERR') or define('STDERR', fopen('php://stderr', 'w'));

class AppenderStd extends AppenderAbstract
{
    const STDOUT = STDOUT;
    const STDERR = STDERR;

    private $stream = self::STDOUT;

    public function write($priority, $message)
    {
        fwrite($this->stream, $message);
    }

    /**
     * @param string $streamName
     *
     * @throws LoggerConfigurationException
     */
    public function setStream($streamName)
    {
        switch ($streamName) {
            case 'STDOUT':
                $this->stream = self::STDOUT;

                return;
            case 'STDERR':
                $this->stream = self::STDERR;

                return;
            default:
                LoggerPolicy::processConfigurationError("Stream must be STDOUT or STDERR, got '{$streamName}'");

                return;
        }
    }
}
