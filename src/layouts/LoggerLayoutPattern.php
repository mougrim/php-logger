<?php
/**
 * Enterprise pattern layout
 *
 * Available patterns
 * - {date} or {date:<format>} standard date() format
 * - {pid} process id
 * - {level} level name, as example INFO
 * - {logger} logger name
 * - {message}
 * - {ex} or {exception}
 * - {location}
 * - {location:file:line}
 * - {location:class:function}
 * - {global:keypath} $GLOBAL variable. Example {global:page.num} returns $GLOBAL['page']['num']
 * - {mdc} or {mdc:key}
 * - {ndc}
 * - {argv}
 * - {callable:get_username} log return value of function <code>string function get_username(){}<code>
 * - {callable:Foo::bar} log return value of method <code>string Foo::bar(){}<code>
 * - {memory} formatted memory_get_usage(true) value with precision 2 and B, KB, MB, GB, TB
 * - {memory:3} set custom precision
 * - {memory:b,kb,mb,gb} set custom precision labels
 * - {memory:4,kb,mb} set custom precision and custom labels
 */
class LoggerLayoutPattern implements LoggerLayoutInterface
{
    const PATTERN_FULL = '{date:Y/m/d} [{level}] {logger} {location:file:line, class.function} {mdc:key} {mdc} {ndc}: {message} {ex}';
    const PATTERN_EXPRESSION = '/\{([a-z]+)(:([^}]+))?\}/i';

    private $pattern = self::PATTERN_FULL;
    private $patternMap = array(
        'date' => 'LoggerPatternDate',
        'pid' => 'LoggerPatternPid',
        'level' => 'LoggerPatternLevel',
        'logger' => 'LoggerPatternLogger',
        'message' => 'LoggerPatternMessage',
        'ex' => 'LoggerPatternException',
        'exception' => 'LoggerPatternException',
        'location' => 'LoggerPatternLocation',
        'global' => 'LoggerPatternGlobal',
        'ndc' => 'LoggerPatternNDC',
        'mdc' => 'LoggerPatternMDC',
        'argv' => 'LoggerPatternArgv',
        'call' => 'LoggerPatternCallable',
        'memory' => 'LoggerMemoryUsage',
    );
    /** @var LoggerPatternInterface[] */
    private $patternMappers = array();

    public function __construct($pattern = null)
    {
        if ($pattern) {
            $this->pattern = $pattern;
        }
        if (preg_match_all(self::PATTERN_EXPRESSION, $this->pattern, $matches)) {
            foreach ($matches[1] as $key => $patternName) {
                $fullMatch = $matches[0][$key];
                $rule = $matches[3][$key];
                if (isset($this->patternMap[$patternName])) {
                    $className = $this->patternMap[$patternName];
                    $this->patternMappers[$fullMatch] = new $className($rule);
                }
            }
        }
    }

    public function formatMessage(Logger $logger, $level, $message, Exception $throwable = null)
    {
        $formatted = $this->pattern;
        foreach ($this->patternMappers as $pattern => $mapper) {
            $formatted = strtr(
                $formatted,
                array(
                    $pattern => $mapper->render($logger, $level, $message, $throwable)
                )
            );
        }
        return $formatted . PHP_EOL;
    }
}

interface LoggerPatternInterface
{
    /**
     * @param Logger $logger
     * @param $level
     * @param $message
     * @param Exception $throwable
     * @return string
     */
    public function render(Logger $logger, $level, $message, Exception $throwable = null);
}

abstract class LoggerPatternPrintFormat implements LoggerPatternInterface
{
    private $printFormat;

    public function __construct($printFormat)
    {
        if ($printFormat) {
            $this->printFormat = (string)$printFormat;
        }
    }

    protected function format($message)
    {
        if ($this->printFormat) {
            return sprintf($this->printFormat, $message);
        } else {
            return $message;
        }
    }
}

class LoggerPatternDate implements LoggerPatternInterface
{
    private $dateFormat;

    public function __construct($dateFormat)
    {
        if ($dateFormat) {
            $this->dateFormat = (string)$dateFormat;
        } else {
            $this->dateFormat = 'Y:m:d';
        }
    }

    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        return date($this->dateFormat);
    }
}

class LoggerPatternPid extends LoggerPatternPrintFormat
{
    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        return $this->format(posix_getpid());
    }
}

class LoggerPatternLevel extends LoggerPatternPrintFormat
{
    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        return $this->format(Logger::getLevelName($level));
    }
}

class LoggerPatternLogger extends LoggerPatternPrintFormat
{
    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        return $this->format($logger->getName());
    }
}

class LoggerPatternMessage extends LoggerPatternPrintFormat
{
    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        $rendered = LoggerRender::render($message);
        return $this->format($rendered);
    }
}

class LoggerPatternException extends LoggerPatternPrintFormat
{
    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        if ($throwable) {
            return (string)$throwable;
        } else {
            return '';
        }
    }
}

class LoggerPatternLocation implements LoggerPatternInterface
{
    private $format = 'file:line';

    public function __construct($format)
    {
        if ($format) {
            $this->format = $format;
        }
    }

    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        $trace = debug_backtrace();
        $prevHop = null;
        // make a down search to identify the caller
        $hop = array_pop($trace);
        while ($hop !== null) {
            if (isset($hop['class'])) {
                // we are sometimes in functions = no class available: avoid php warning here
                $className = $hop['class'];
                if (!empty($className) && strpos($className, 'Logger') === 0) {
                    $locationInfo['line'] = $hop['line'];
                    $locationInfo['file'] = $hop['file'];
                    break;
                }
            }
            $prevHop = $hop;
            $hop = array_pop($trace);
        }
        $locationInfo['class'] = isset($prevHop['class']) ? $prevHop['class'] : 'main';
        if (isset($prevHop['function']) and
            $prevHop['function'] !== 'include' and
            $prevHop['function'] !== 'include_once' and
            $prevHop['function'] !== 'require' and
            $prevHop['function'] !== 'require_once'
        ) {
            $locationInfo['function'] = $prevHop['function'];
        } else {
            $locationInfo['function'] = 'main';
        }
        return strtr($this->format, $locationInfo);
    }
}

class LoggerPatternGlobal implements LoggerPatternInterface
{
    private $path = array();

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
            $this->path = array();
        }
    }

    public function render(Logger $logger, $level, $message, Exception $throwable = null)
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

class LoggerPatternNDC implements LoggerPatternInterface
{
    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        if ($ndc = LoggerNDC::getStack()) {
            return join(' ', $ndc);
        } else {
            return '';
        }
    }
}

class LoggerPatternMDC implements LoggerPatternInterface
{
    private $key;

    public function __construct($key)
    {
        $this->key = (string)$key;
    }

    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        if ($this->key) {
            return LoggerRender::render(LoggerMDC::get($this->key));
        }
        $formatted = '';
        if ($mdc = LoggerMDC::getMap()) {
            foreach ($mdc as $key => $context) {
                $formatted .= $key . '=' . $context . ' ';
            }
        }
        // @todo optimize this sheet
        return rtrim($formatted);
    }
}

class LoggerPatternArgv extends LoggerPatternPrintFormat
{
    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        global $argv;
        return $this->format(join(' ', is_array($argv) ? $argv : array()));
    }
}

class LoggerPatternCallable implements LoggerPatternInterface
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
     * @param Logger $logger
     * @param $level
     * @param $message
     * @param Exception $throwable
     * @return string
     */
    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        if (is_callable($this->callable)) {
            return LoggerRender::render(call_user_func($this->callable));
        } else {
            return LoggerRender::render(null);
        }
    }
}

class LoggerMemoryUsage implements LoggerPatternInterface
{
    private $units = array('B', 'KB', 'MB', 'GB', 'TB');
    private $precision = 2;

    public function __construct($string)
    {
        if (strpos($string, ',') !== false) {
            $keys = preg_split('/,/', $string, -1, PREG_SPLIT_NO_EMPTY);
            if (is_numeric($keys[0])) {
                $this->precision = (int)$keys[0];
                unset($keys[0]);
            }
            $this->units = array_values($keys);
        } elseif (is_numeric($string)) {
            $this->precision = (int)$string;
        }
    }

    /**
     * @param Logger $logger
     * @param $level
     * @param $message
     * @param Exception $throwable
     * @return string
     */
    public function render(Logger $logger, $level, $message, Exception $throwable = null)
    {
        $bytes = memory_get_usage(true);
        $base = log($bytes) / log(1024);
        $suffix = min((int)floor($base), count($this->units) - 1);
        return round(pow(1024, $base - $suffix), $this->precision) . $this->units[$suffix];
    }
}