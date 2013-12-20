<?php

class LoggerTimer
{
    /** @var Logger */
    private $logger;
    private $start;

    public function __construct(Logger $logger, $start)
    {
        $this->start = (float)$start;
        $this->logger = $logger;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getTime()
    {
        return microtime(true) - $this->start;
    }

    public function trace($message, Exception $throwable = null)
    {
        $this->logger->log(Logger::TRACE, $this->render($message, microtime(true), $throwable));
    }

    public function debug($message, Exception $throwable = null)
    {
        $this->logger->log(Logger::DEBUG, $this->render($message, microtime(true), $throwable));
    }

    public function info($message, Exception $throwable = null)
    {
        $this->logger->log(Logger::INFO, $this->render($message, microtime(true), $throwable));
    }

    public function warn($message, Exception $throwable = null)
    {
        $this->logger->log(Logger::WARN, $this->render($message, microtime(true), $throwable));
    }

    public function error($message, Exception $throwable = null)
    {
        $this->logger->log(Logger::ERROR, $this->render($message, microtime(true), $throwable));
    }

    public function fatal($message, Exception $throwable = null)
    {
        $this->logger->log(Logger::FATAL, $this->render($message, microtime(true), $throwable));
    }

    public function log($level, $message, Exception $throwable = null)
    {
        $this->logger->log($level, $this->render($message, microtime(true), $throwable));
    }

    public function render($message, $end)
    {
        return strtr($message, array(
            '{start}' => $this->start,
            '{end}' => $end,
            '{time}' => $end - $this->start,
        ));
    }
} 