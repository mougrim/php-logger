<?php
namespace Mougrim\Logger\Appender;

use Mougrim\Logger\Layout\LayoutInterface;
use Mougrim\Logger\Logger;

abstract class AppenderAbstract
{
    protected $minLevel;
    protected $maxLevel;
    /** @var LayoutInterface|null */
    protected $layout;

    abstract public function write($level, $message);

    public function append(Logger $logger, $level, $message, \Exception $throwable = null)
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

    public function setLayout(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }
}
