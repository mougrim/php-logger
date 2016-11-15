<?php

namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerPolicy;
use Mougrim\Logger\LoggerRender;

class PatternGlobal implements PatternInterface
{
    private $path = [];

    public function __construct($path)
    {
        if ($path) {
            $this->path = preg_split('/\./', $path, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (!$this->path) {
            LoggerPolicy::processConfigurationError('path is required');
            $this->path = [];
        }
    }

    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        $current = $GLOBALS;
        foreach ($this->path as $key) {
            if (!isset($current[$key])) {
                $current = null;
                break;
            }
            $current = $current[$key];
        }

        return LoggerRender::render($current);
    }
}
