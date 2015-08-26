<?php
namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerConfigurationException;
use Mougrim\Logger\LoggerPolicy;
use Mougrim\Logger\LoggerRender;

class PatternGlobal implements PatternInterface
{
    private $path = [];

    public function __construct($path)
    {
        if ($path) {
            $this->path = preg_split('/\./', $path, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (!$this->path) {
            $message = 'path is required';
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
            $this->path = [];
        }
    }

    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        $current = $GLOBALS;
        foreach ($this->path as $key) {
            if (isset($current[$key])) {
                $current = $current[$key];
            } else {
                $current = null;
                break;
            }
        }

        return LoggerRender::render($current);
    }
}
