<?php

namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;

class PatternDate implements PatternInterface
{
    private $dateFormat;

    public function __construct($dateFormat)
    {
        $this->dateFormat = 'Y:m:d';
        if ($dateFormat) {
            $this->dateFormat = (string) $dateFormat;
        }
    }

    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        return date($this->dateFormat);
    }
}
