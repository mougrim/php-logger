<?php

namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;

interface PatternInterface
{
    /**
     * @param Logger $logger
     * @param $level
     * @param $message
     * @param \Exception $throwable
     *
     * @return string
     */
    public function render(Logger $logger, $level, $message, \Exception $throwable = null);
}
