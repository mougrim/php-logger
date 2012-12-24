<?php

if (!defined('PHP_INT_MIN')) {
    define('PHP_INT_MIN', PHP_INT_MAX * -1 - 1);
}


/**
 * Example of usage:
 *
 * <pre>
 *  Logger::configure(array(
 *      'layouts' => array(
 *          'simple' => array(
 *              'class' => 'LoggerLayoutSimple',
 *          ),
 *          'pattern' => array(
 *              'class' => 'LoggerLayoutPattern',
 *              'pattern' => '{date:Y/m/d} [{level}] {logger} {file}:{line} {class}:{function} {mdc:key} {mdc} {ndc}: {message} {ex}',
 *          ),
 *      ),
 *      'appenders' => array(
 *          'stream' => array(
 *              'class' => 'LoggerAppenderStream',
 *              'stream' => 'php://stdout',
 *              'useLock' => true,
 *              'useLockShortMessage' => false,
 *              'minLevel' => 0,
 *              'maxLevel' => PHP_INT_MAX,
 *              'layout' => 'simple',
 *          ),
 *      ),
 *      'loggers' => array(
 *          'logger' => array(
 *              'appenders' => array('stream'),
 *              'addictive' => false,
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
    /*
     * Logger factory description
     */

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
        if (self::$isConfigured) {
            self::configure();
        }
        return self::$hierarchy->getLogger($name);
    }

    /**
     * Returns the Root Logger.
     * @return LoggerRoot
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

    public static function reset()
    {
        self::$hierarchy = new LoggerHierarchy();
        self::$isConfigured = false;
        LoggerNDC::clear();
        LoggerMDC::clear();
    }

    public static function getLevelName($level)
    {
        $level = (int)$level;
        switch (true) {
            case $level >= Logger::OFF:
                return 'OFF';
            case $level >= Logger::FATAL:
                return 'FATAL';
            case $level >= Logger::ERROR:
                return 'ERROR';
            case $level >= Logger::WARN:
                return 'WARN';
            case $level >= Logger::INFO:
                return 'INFO';
            case $level >= Logger::DEBUG:
                return 'DEBUG';
            case $level >= Logger::TRACE:
                return 'TRACE';
            case $level >= Logger::ALL:
                return 'ALL';
            default:
                throw new LoggerException('O.o wtf?!');
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
    private $additive = true;

    public function __construct($name, Logger $parent = null)
    {
        $this->name = (string)$name;
        $this->parent = $parent;
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
        if ($key) {
            unset($this->appenders[$key]);
        }
    }

    public function getAppenders()
    {
        return $this->appenders;
    }

    public function setAdditive($additive)
    {
        $this->additive = (bool)$additive;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParent()
    {
        return $this->parent;
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
        foreach ($this->appenders as $appender) {
            $appender->append($this, $level, $message, $throwable);
        }
        if ($this->parent && $this->additive) {
            $this->parent->log($level, $message, $throwable);
        }
    }
}