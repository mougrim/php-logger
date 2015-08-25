<?php
namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;

class PatternDate implements PatternInterface
{
    private $dateFormat;

    public function __construct($dateFormat)
    {
        if ($dateFormat) {
            $this->dateFormat = (string)$dateFormat;
        } else {
            $this->dateFormat = 'Y:m:d';
        }
    }

    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        return date($this->dateFormat);
    }
}
