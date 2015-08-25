<?php
/**
 * @author Mougrim <rinat@mougrim.ru>
 */

namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;

class PatternArgv extends PatternPrintFormat
{
    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        global $argv;

        return $this->format(join(' ', is_array($argv) ? $argv : []));
    }
}
