<?php
namespace Mougrim\Logger\Appender;

use Mougrim\Logger\LoggerIOException;
use Mougrim\Logger\LoggerPolicy;

class AppenderSocket extends AppenderAbstract
{
    private $host;
    private $port;
    private $timeout;

    public function __construct($host, $port, $timeout = null)
    {
        $this->host = (string)$host;
        $this->port = (int)$port;
        if ($timeout) {
            $this->timeout = (int)$timeout;
        } else {
            $this->timeout = (int)ini_get("default_socket_timeout");
        }
    }

    public function write($priority, $message)
    {
        $socket = fsockopen($this->host, $this->port, $errorCode, $errorMessage, $this->timeout);
        if ($socket === false) {
            $message = "Could not open socket to {$this->host}:{$this->port} – {$errorCode} {$errorMessage}. Closing appender.";
            switch (LoggerPolicy::getIOErrorPolicy()) {
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
                    throw new LoggerIOException($message);
            }
            return;
        }
        $write = fwrite($socket, $message);
        fclose($socket);
        if (false === $write) {
            $message = "Error writing to socket to {$this->host}:{$this->port}";
            switch (LoggerPolicy::getIOErrorPolicy()) {
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
                    throw new LoggerIOException($message);
            }
            return;
        }
    }
}
