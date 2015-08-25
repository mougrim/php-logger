<?php
/**
 * @author Mougrim <rinat@mougrim.ru>
 */

namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;

class PatternException extends PatternPrintFormat
{
    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        if ($throwable) {
            return (string) $throwable;
        } else {
            return '';
        }
    }
}
