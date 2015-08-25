<?php
namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;

class PatternPid extends PatternPrintFormat
{
    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        return $this->format(posix_getpid());
    }
}
