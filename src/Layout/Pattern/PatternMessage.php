<?php
namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerRender;

class PatternMessage extends PatternPrintFormat
{
    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        $rendered = LoggerRender::render($message);

        return $this->format($rendered);
    }
}
