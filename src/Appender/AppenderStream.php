<?php
namespace Mougrim\Logger\Appender;

use Mougrim\Logger\LoggerIOException;
use Mougrim\Logger\LoggerPolicy;

class AppenderStream extends AppenderAbstract implements AppenderReopen
{
    /**
     * @deprecated should remove in next major version
     */
    const MESSAGE_THRESHOLD = 4096;

    /** @var string */
    private $streamUrl;
    /** @var resource */
    private $stream;
    /** @var bool */
    private $useLock = true;
    /**
     * https://github.com/mougrim/php-logger/issues/4
     * @deprecated should remove in next major version
     * @var bool <strike>if message less than 4096, do not use locks</strike><br>
     * don't use lock
     */
    private $useLockShortMessage = false;

    public function __construct($stream)
    {
        $this->streamUrl = $stream;
        $this->getStream();
    }

    public function __destruct()
    {
        $this->close();
    }

    public function reopen()
    {
        $this->close();
        $this->getStream();
    }

    public function close()
    {
        if ($this->stream) {
            fclose($this->stream);
            $this->stream = null;
        }
    }

    public function write($priority, $message)
    {
        $steam = $this->getStream();
        if (!$steam) {
            return;
        }
        // useLockShortMessage should remove in next major version
        if (!$this->useLock || !$this->useLockShortMessage) {
            fwrite($steam, $message);
            return;
        }
        if (!flock($steam, LOCK_EX)) {
            LoggerPolicy::processIOError('Error get lock');
            fwrite($steam, $message);
            return;
        }
        fwrite($steam, $message);
        flock($steam, LOCK_UN);
    }

    /**
     * @param boolean $useLock
     */
    public function setUseLock($useLock)
    {
        $this->useLock = (bool)$useLock;
    }

    /**
     * @deprecated should remove in next major version
     * @param boolean $useLockShortMessage pass true for use lock
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
        if ($this->stream && get_resource_type($this->stream) === 'Unknown') {
            fclose($this->stream);
            $this->stream = null;
        }
        if (!$this->stream) {
            $this->stream = @fopen($this->streamUrl, 'a');
            if (!$this->stream) {
                LoggerPolicy::processIOError("Error open $this->streamUrl");
                $this->stream = null;
            }
        }
        return $this->stream;
    }
}
