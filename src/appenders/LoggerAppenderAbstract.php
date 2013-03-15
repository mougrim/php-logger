<?php

abstract class LoggerAppenderAbstract
{
    protected $minLevel;
    protected $maxLevel;
    /** @var LoggerLayoutInterface|null */
    protected $layout = null;

    abstract public function write($level, $message);

    public function append(Logger $logger, $level, $message, Exception $throwable = null)
    {
        if ($this->minLevel !== null && $level < $this->minLevel) {
            return;
        }
        if ($this->maxLevel !== null && $level > $this->maxLevel) {
            return;
        }
        if ($this->layout) {
            $message = $this->layout->formatMessage($logger, $level, $message, $throwable);
        }
        $this->write($level, $message);
    }

    public function setMaxLevel($maxLevel)
    {
        $this->maxLevel = (int)$maxLevel;
    }

    public function setMinLevel($minLevel)
    {
        $this->minLevel = (int)$minLevel;
    }

    public function setLayout(LoggerLayoutInterface $layout)
    {
        $this->layout = $layout;
    }
}