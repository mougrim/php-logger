<?php
/**
 * @author Mougrim <rinat@mougrim.ru>
 */

namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerConfigurationException;
use Mougrim\Logger\LoggerPolicy;
use Mougrim\Logger\LoggerRender;

class PatternCallable implements PatternInterface
{
    private $callable;

    public function __construct($callableString)
    {
        if (is_callable($callableString)) {
            $this->callable = $callableString;
        } else {
            $message = "'$callableString' is not callable";
            switch (LoggerPolicy::getConfigurationErrorPolicy()) {
                case LoggerPolicy::POLICY_IGNORE:
                    break;
                case LoggerPolicy::POLICY_TRIGGER_WARN:
                    trigger_error($message, E_USER_WARNING);
                    break;
                case LoggerPolicy::POLICY_TRIGGER_ERROR:
                    trigger_error($message, E_USER_ERROR);
                    break;
                case LoggerPolicy::POLICY_EXIT:
                    exit($message);
                case LoggerPolicy::POLICY_EXCEPTION:
                default:
                    throw new LoggerConfigurationException($message);
            }
        }
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
        if (is_callable($this->callable)) {
            return LoggerRender::render(call_user_func($this->callable));
        } else {
            return LoggerRender::render(null);
        }
    }
}
