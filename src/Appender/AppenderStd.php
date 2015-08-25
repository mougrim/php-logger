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
     * @throws LoggerConfigurationException
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
                $message = "Stream must be STDOUT or STDERR, got '{$streamName}'";
                switch (LoggerPolicy::getConfigurationErrorPolicy()) {
                    case LoggerPolicy::POLICY_IGNORE:
                        break;
                    case LoggerPolicy::POLICY_TRIGGER_WARN:
                        trigger_error($message, E_USER_WARNING);
                        break;
                    case LoggerPolicy::POLICY_TRIGGER_ERROR:
                        trigger_error($message, E_USER_ERROR);
                        break;
                    case LoggerPolicy::POLICY_EXIT:
                        exit($message);
                    case LoggerPolicy::POLICY_EXCEPTION:
                    default:
                        throw new LoggerConfigurationException($message);
                }
                break;
        }
    }
}
