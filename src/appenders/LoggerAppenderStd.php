<?php

defined('STDOUT') or define('STDOUT', fopen('php://stdout', 'w'));
defined('STDERR') or define('STDERR', fopen('php://stderr', 'w'));

class LoggerAppenderStd extends LoggerAppenderAbstract
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
     * @throws LoggerIOException
     */
    public function setStream($streamName)
    {
        switch ($streamName) {
            case 'STDOUT':
                $this->stream = self::STDOUT;
                break;
            case 'STDERR':
                $this->stream = self::STDERR;
                break;
            default:
                throw new LoggerIOException("Stream must be STDOUT or STDERR, got '{$streamName}'");
                break;
        }
    }
}
