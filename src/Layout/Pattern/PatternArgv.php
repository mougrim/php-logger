<?php
namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;

class PatternArgv extends PatternPrintFormat
{
    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        $argv = $GLOBALS['argv'] ?: [];

        return $this->format(implode(' ', is_array($argv) ? $argv : []));
    }
}
