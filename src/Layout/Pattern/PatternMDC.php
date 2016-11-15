<?php

namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerMDC;
use Mougrim\Logger\LoggerRender;

class PatternMDC implements PatternInterface
{
    private $key;

    public function __construct($key)
    {
        $this->key = (string) $key;
    }

    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        if ($this->key) {
            return LoggerRender::render(LoggerMDC::get($this->key));
        }
        $mdc = LoggerMDC::getMap();
        if (!$mdc) {
            return '';
        }
        $formatted = [];
        foreach ($mdc as $key => $context) {
            $formatted[] = $key.'='.$context;
        }

        return implode(' ', $formatted);
    }
}
