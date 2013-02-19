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
 */
class LoggerLayoutPattern implements LoggerLayoutInterface
{
    const PATTERN_FULL = '{date:Y/m/d} [{level}] {logger} {location:file:line, class.function} {mdc:key} {mdc} {ndc}: {message} {ex}';
    const PATTERN_EXPRESSION = '/\{([a-z]+)(:([^}]+))?\}/i';

    private $pattern = self::PATTERN_FULL;
    private $template;
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
    );
    /** @var LoggerPatternInterface[] */
    private $patternMappers = array();

    public function __construct($pattern = null)
    {
        if ($pattern) {
            $this->pattern = $pattern;
        }
        $template = $pattern;
        if (preg_match_all(self::PATTERN_EXPRESSION, $this->pattern, $matches)) {
            foreach ($matches[1] as $key => $patternName) {
                $fullMatch = $matches[0][$key];
                $rule = $matches[3][$key];
                if (isset($this->patternMap[$patternName])) {
                    $className = $this->patternMap[$patternName];
                    $template = strtr($template, array($fullMatch => '{' . $patternName . '}'));
                    $this->patternMappers[$patternName] = new $className($rule);
                }
            }
        }
        $this->template = $template;
    }

    public function formatMessage(Logger $logger, $level, $message, Exception $throwable = null)
    {
        $formatted = $this->template;
        foreach ($this->patternMappers as $name => $mapper) {
            $formatted = strtr(
                $formatted,
                array(
                    '{' . $name . '}' => $mapper->render($logger, $level, $message, $throwable)
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
            throw new LoggerException('path is required');
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
            return (string)LoggerMDC::get($this->key);
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