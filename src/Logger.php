<?php

if (!defined('PHP_INT_MIN')) {
    define('PHP_INT_MIN', PHP_INT_MAX * -1 - 1);
}


/**
 * Example of usage:
 *
 * <pre>
 *  Logger::configure(array(
 *      'policy' => array(
 *          'ioError' => 'trigger_error', // ignore, trigger_warn, trigger_error, exception or exit
 *          'configurationError' => 'exception'
 *      ),
 *      'renderer' => array(
 *          'nullMessage' => 'null',
 *          'trueMessage' => 'true',
 *          'falseMessage' => 'false',
 *      )
 *      'layouts' => array(
 *          'simple' => array(
 *              'class' => 'LoggerLayoutSimple',
 *          ),
 *          'pattern' => array(
 *              'class' => 'LoggerLayoutPattern',
 *              'pattern' => '{date:Y/m/d} [{level}] {logger} {location:file:line, class.function} {mdc:key} {mdc} {ndc}: {message} {ex}',
 *          ),
 *      ),
 *      'appenders' => array(
 *          'stream' => array(
 *              'class' => 'LoggerAppenderStream',
 *              'stream' => 'php://stdout',
 *              'useLock' => true,
 *              'useLockShortMessage' => false,
 *              'minLevel' => Logger::TRACE,
 *              'maxLevel' => Logger::FATAL,
 *              'layout' => 'simple',
 *          ),
 *      ),
 *      'loggers' => array(
 *          'logger' => array(
 *              'appenders' => array('stream'),
 *              'addictive' => false,
 *              'minLevel' => Logger::DEBUG,
 *              'maxLevel' => Logger::FATAL,
 *          ),
 *      ),
 *      'root' => array(
 *          'appenders' => array('stream'),
 *      )
 * ));
 * Logger::getLogger('logger')->info("hello world");
 * </pre>
 */
class Logger
{
    const OFF = PHP_INT_MAX;
    const FATAL = 50000;
    const ERROR = 40000;
    const WARN = 30000;
    const INFO = 20000;
    const DEBUG = 10000;
    const TRACE = 5000;
    const ALL = PHP_INT_MIN;

    /** @var LoggerHierarchy */
    private static $hierarchy;
    private static $isConfigured = false;

    /**
     * Returns a Logger by name. If it does not exist, it will be created.
     *
     * @param string $name The logger name
     * @return Logger
     */
    public static function getLogger($name)
    {
        if (!self::$isConfigured) {
            self::configure();
        }
        return self::$hierarchy->getLogger($name);
    }

    /**
     * Returns the Root Logger.
     * @return Logger
     */
    public static function getRootLogger()
    {
        if (!self::$isConfigured) {
            self::configure();
        }
        return self::$hierarchy->getRootLogger();
    }

    /**
     * @param null $configuration
     */
    public static function configure($configuration = null)
    {
        self::reset();
        if (is_string($configuration) && is_file($configuration)) {
            if (preg_match('/\.php$/', $configuration)) {
                $configuration = require $configuration;
            }
        }
        if (is_array($configuration)) {
            $configurator = new LoggerConfigurator();
            $configurator->configure(self::$hierarchy, $configuration);
        }
        self::$isConfigured = true;
    }

    /**
     * Reset all loggers, appenders, etc
     */
    public static function reset()
    {
        self::$hierarchy = new LoggerHierarchy();
        self::$isConfigured = false;
        LoggerNDC::clear();
        LoggerMDC::clear();
        LoggerRender::reset();
        LoggerPolicy::reset();
    }

    /**
     * Reopen log appender stream, what have support
     * Usable in forks, SIGHUP handlers etc.
     */
    public static function reopen()
    {
        if (!self::$isConfigured) {
            self::configure();
        }
        self::$hierarchy->reopen();
    }

    /**
     * @param int $level
     * @return string
     */
    public static function getLevelName($level)
    {
        $level = (int)$level;
        switch (true) {
            case $level >= self::OFF:
                return 'OFF';
            case $level >= self::FATAL:
                return 'FATAL';
            case $level >= self::ERROR:
                return 'ERROR';
            case $level >= self::WARN:
                return 'WARN';
            case $level >= self::INFO:
                return 'INFO';
            case $level >= self::DEBUG:
                return 'DEBUG';
            case $level >= self::TRACE:
                return 'TRACE';
            default:
                return 'ALL';
        }
    }

    /**
     * @param string $level
     * @return int
     */
    public static function getLevelByName($level)
    {
        $level = strtoupper($level);
        switch (true) {
            case $level === 'OFF':
                return self::OFF;
            case $level === 'FATAL':
                return self::FATAL;
            case $level === 'ERROR':
                return self::ERROR;
            case $level === 'WARN':
                return self::WARN;
            case $level === 'INFO':
                return self::INFO;
            case $level === 'DEBUG':
                return self::DEBUG;
            case $level === 'TRACE':
                return self::TRACE;
            default:
                return self::ALL;
        }
    }

    /*
     * Logger object description
     */

    private $name;
    /** @var \Logger */
    private $parent;
    /** @var LoggerAppenderAbstract[] */
    private $appenders = array();
    /** @var bool */
    private $addictive = true;
    private $minLevel;
    private $maxLevel;

    public function __construct($name, Logger $parent = null)
    {
        $this->name = (string)$name;
        $this->parent = $parent;
    }

    /**
     * @param mixed $minLevel
     */
    public function setMinLevel($minLevel)
    {
        $this->minLevel = (int)$minLevel;
    }

    /**
     * @param mixed $maxLevel
     */
    public function setMaxLevel($maxLevel)
    {
        $this->maxLevel = (int)$maxLevel;
    }

    public function addAppender(LoggerAppenderAbstract $appender)
    {
        if (!in_array($appender, $this->appenders)) {
            $this->appenders[] = $appender;
        }
    }

    public function removeAppender(LoggerAppenderAbstract $appender)
    {
        $key = array_search($appender, $this->appenders);
        if ($key !== false) {
            unset($this->appenders[$key]);
        }
    }

    public function getAppenders()
    {
        return $this->appenders;
    }

    public function setAddictive($addictive)
    {
        $this->addictive = (bool)$addictive;
    }

    public function getAddictive()
    {
        return $this->addictive;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Simple timer, use like:
     * <code>
     * $timer = $logger->timer();
     * sleep(10);
     * $timer->info("sleep {time} seconds");
     * </code>
     * @return LoggerTimer
     */
    public function timer()
    {
        return new LoggerTimer($this, microtime(1));
    }

    public function trace($message, Exception $throwable = null)
    {
        $this->log(self::TRACE, $message, $throwable);
    }

    public function debug($message, Exception $throwable = null)
    {
        $this->log(self::DEBUG, $message, $throwable);
    }

    public function info($message, Exception $throwable = null)
    {
        $this->log(self::INFO, $message, $throwable);
    }

    public function warn($message, Exception $throwable = null)
    {
        $this->log(self::WARN, $message, $throwable);
    }

    public function error($message, Exception $throwable = null)
    {
        $this->log(self::ERROR, $message, $throwable);
    }

    public function fatal($message, Exception $throwable = null)
    {
        $this->log(self::FATAL, $message, $throwable);
    }

    public function log($level, $message, Exception $throwable = null)
    {
        if (!($this->minLevel !== null && $level < $this->minLevel) &&
            !($this->maxLevel !== null && $level > $this->maxLevel)
        ) {
            foreach ($this->appenders as $appender) {
                $appender->append($this, $level, $message, $throwable);
            }
        }
        if ($this->addictive && $this->parent) {
            if (!($this->parent->minLevel !== null && $level < $this->parent->minLevel) &&
                !($this->parent->maxLevel !== null && $level > $this->parent->maxLevel)
            ) {
                // do not call parent->log, too slow
                foreach ($this->parent->appenders as $appender) {
                    $appender->append($this, $level, $message, $throwable);
                }
            }
        }
    }
}