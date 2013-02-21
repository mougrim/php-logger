<?php

/**
 * Class logger LoggerAppenderStreamBuffer implements log buffer with threshold.
 *
 * If appender have event with level less than threshold, it puts event to in memory buffer.
 * If appender have event with level greater or equal than threshold, it puts buffered messages to stream.
 *
 * Example of use:
 *
 * $appender = new LoggerAppenderStreamBuffer('php://stdout');
 * $appender->setThreshold(Logger::ERROR);
 * $appender->append($logger, Logger::DEBUG, 'debug'); // no output
 * $appender->append($logger, Logger::INFO, 'info');   // no output
 * $appender->append($logger, Logger::ERROR, 'error'); // outputs "debug\ninfo\nerror\n"
 *
 */

if (class_exists('SplDoublyLinkedList', false)) { // like php 5.3 +

    class LoggerAppenderStreamBuffer extends LoggerAppenderStream
    {
        private $buffer;
        private $threshold = Logger::ERROR;

        public function __construct($stream)
        {
            $this->buffer = new SplDoublyLinkedList();
            parent::__construct($stream);
        }

        public function append(Logger $logger, $level, $message, Exception $throwable = null)
        {
            if ($this->layout) {
                $message = $this->layout->formatMessage($logger, $level, $message, $throwable);
            }
            $this->buffer->push($message);
            if ($level >= $this->threshold) {
                while (!$this->buffer->isEmpty() && ($message = $this->buffer->shift())) {
                    $this->write($level, $message);
                }
            }
        }

        public function setThreshold($threshold)
        {
            $this->threshold = (int)$threshold;
        }
    }

} else { // like php 5.2

    class LoggerAppenderStreamBuffer extends LoggerAppenderStream
    {
        private $buffer = array();
        private $threshold = Logger::ERROR;

        public function append(Logger $logger, $level, $message, Exception $throwable = null)
        {
            if ($this->layout) {
                $message = $this->layout->formatMessage($logger, $level, $message, $throwable);
            }
            array_push($this->buffer, $message);
            if ($level >= $this->threshold) {
                while ($message = array_shift($this->buffer)) {
                    $this->write($level, $message);
                }
            }
        }

        public function setThreshold($threshold)
        {
            $this->threshold = (int)$threshold;
        }
    }

}