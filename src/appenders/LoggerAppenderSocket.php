<?php

class LoggerAppenderSocket extends LoggerAppenderAbstract
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
            throw new LoggerIOException("Could not open socket to {$this->host}:{$this->port} – {$errorCode} {$errorMessage}. Closing appender.");
        }
        if (false === fwrite($socket, $message)) {
            throw new LoggerIOException("Error writing to socket to {$this->host}:{$this->port}");
        }
        fclose($socket);
    }
}
