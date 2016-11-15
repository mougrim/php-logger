<?php

namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerNDC;

class PatternNDC implements PatternInterface
{
    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        $ndc = LoggerNDC::getStack();
        if (!$ndc) {
            return '';
        }

        return implode(' ', $ndc);
    }
}
