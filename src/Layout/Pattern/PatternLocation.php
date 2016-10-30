<?php
namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;

class PatternLocation implements PatternInterface
{
    private $format = 'file:line';

    public function __construct($format)
    {
        if ($format) {
            $this->format = $format;
        }
    }

    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        $trace   = debug_backtrace();
        $prevHop = null;
        // make a down search to identify the caller
        $hop = array_pop($trace);
        while ($hop !== null) {
            if (isset($hop['line']) && isset($hop['class'])) {
                // we are sometimes in functions = no class available: avoid php warning here
                $className = $hop['class'];
                if (!empty($className) && strpos($className, 'Mougrim\\Logger\\') === 0) {
                    $locationInfo['line'] = $hop['line'];
                    $locationInfo['file'] = $hop['file'];
                    break;
                }
                // for backward compatibility with first version
                if (!empty($className) && strpos($className, 'Logger') === 0) {
                    $locationInfo['line'] = $hop['line'];
                    $locationInfo['file'] = $hop['file'];
                    break;
                }
            }
            $prevHop = $hop;
            $hop     = array_pop($trace);
        }
        $locationInfo['class'] = isset($prevHop['class']) ? $prevHop['class'] : 'main';
        if (isset($prevHop['function']) &&
            $prevHop['function'] !== 'include' &&
            $prevHop['function'] !== 'include_once' &&
            $prevHop['function'] !== 'require' &&
            $prevHop['function'] !== 'require_once'
        ) {
            $locationInfo['function'] = $prevHop['function'];
        } else {
            $locationInfo['function'] = 'main';
        }

        return strtr($this->format, $locationInfo);
    }
}
