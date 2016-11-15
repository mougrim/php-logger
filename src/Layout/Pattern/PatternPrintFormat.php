<?php

namespace Mougrim\Logger\Layout\Pattern;

abstract class PatternPrintFormat implements PatternInterface
{
    private $printFormat;

    public function __construct($printFormat)
    {
        if ($printFormat) {
            $this->printFormat = (string) $printFormat;
        }
    }

    protected function format($message)
    {
        if ($this->printFormat) {
            return sprintf($this->printFormat, $message);
        } else {
            return $message;
        }
    }
}
