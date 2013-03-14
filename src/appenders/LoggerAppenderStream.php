<?php

class LoggerAppenderStream extends LoggerAppenderAbstract
{
    const MESSAGE_THRESHOLD = 4096;

    /** @var string */
    private $streamUrl;
    /** @var resource */
    private $stream;
    /** @var bool */
    private $useLock = true;
    /** @var bool if message less than 4096, do not use locks */
    private $useLockShortMessage = false;

    public function __construct($stream)
    {
        $this->streamUrl = $stream;
        $this->getStream();
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1) ;
            pcntl_signal(SIGHUP, array($this, 'reopen'));
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function reopen()
    {
        error_log("log {$this->stream} reopened");
        $this->close();
        $this->getStream();
    }

    public function close()
    {
        if ($this->stream) {
            fclose($this->stream);
        }
    }

    public function write($priority, $message)
    {
        $steam = $this->getStream();
        if ($this->useLock) {
            if (!$this->useLockShortMessage && strlen($message) <= self::MESSAGE_THRESHOLD) {
                fwrite($steam, $message);
            } else if (flock($steam, LOCK_EX)) {
                fwrite($steam, $message);
                flock($steam, LOCK_UN);
            } else throw new LoggerIOException('Error get lock');
        } else {
            fwrite($steam, $message);
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

    /**
     * @return resource
     * @throws LoggerIOException
     */
    private function getStream()
    {
        if (is_resource($this->stream) && get_resource_type($this->stream) == 'Unknown') {
            fclose($this->stream);
            $this->stream = null;
        }
        if ($this->stream === null || !is_resource($this->stream)) {
            $this->stream = @fopen($this->streamUrl, 'a');
            if (!$this->stream) {
                throw new LoggerIOException("Error open $this->streamUrl");
            }
        }
        return $this->stream;
    }
}
