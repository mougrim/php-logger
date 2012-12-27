<?php

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
     * @throws InvalidArgumentException
     * @return void
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
