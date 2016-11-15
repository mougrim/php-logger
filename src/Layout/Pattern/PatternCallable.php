<?php

namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerPolicy;
use Mougrim\Logger\LoggerRender;

class PatternCallable implements PatternInterface
{
    private $callable;

    public function __construct($callableString)
    {
        if (!is_callable($callableString)) {
            LoggerPolicy::processConfigurationError("'$callableString' is not callable");

            return;
        }
        $this->callable = $callableString;
    }

    /**
     * @param Logger     $logger
     * @param            $level
     * @param            $message
     * @param \Exception $throwable
     *
     * @return string
     */
    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        if (!is_callable($this->callable)) {
            return LoggerRender::render(null);
        }

        return LoggerRender::render(call_user_func($this->callable));
    }
}
