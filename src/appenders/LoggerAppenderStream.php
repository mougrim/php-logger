<?php

class LoggerAppenderStream extends LoggerAppenderAbstract
{
    const MESSAGE_THRESHOLD = 4096;

    /** @var resource */
    private $stream;
    /** @var bool */
    private $useLock = true;
    /** @var bool if message less than 4096, do not use locks */
    private $useLockShortMessage = false;

    public function __construct($stream)
    {
        // use @ for ignore invalid stream errors
        $this->stream = @fopen($stream, 'a');
        if (!$this->stream) {
            throw new LoggerIOException("Error open $stream");
        }
    }

    public function __destruct()
    {
        if ($this->stream) {
            fclose($this->stream);
        }
    }

    public function write($priority, $message)
    {
        if ($this->useLock) {
            if (!$this->useLockShortMessage && strlen($message) <= self::MESSAGE_THRESHOLD) {
                fwrite($this->stream, $message);
            } else if (flock($this->stream, LOCK_EX)) {
                fwrite($this->stream, $message);
                flock($this->stream, LOCK_UN);
            } else throw new LoggerIOException('Error get lock');
        } else {
            fwrite($this->stream, $message);
        }
    }

    /**
     * @param boolean $useLock
     */
    public function setUseLock($useLock)
    {
        $this->useLock = (bool)$useLock;
    }

    /**
     * @param boolean $useLockShortMessage
     */
    public function setUseLockShortMessage($useLockShortMessage)
    {
        $this->useLockShortMessage = (bool)$useLockShortMessage;
    }
}
