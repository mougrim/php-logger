<?php
namespace Mougrim\Logger;

use Mougrim\Logger\Appender\AppenderAbstract;

if (!defined('PHP_INT_MIN')) {
    define('PHP_INT_MIN', PHP_INT_MAX * -1 - 1);
}


/**
 * Example of usage:
 *
 * <pre>
 * use Mougrim\Logger\Appender\AppenderStream;
 * use Mougrim\Logger\Layout\LayoutPattern;
 * use Mougrim\Logger\Layout\LayoutSimple;
 * use Mougrim\Logger\Logger;
 *
 *  Logger::configure([
 *      'policy' => [
 *          'ioError' => 'trigger_error', // ignore, trigger_warn, trigger_error, exception or exit
 *          'configurationError' => 'exception'
 *      ],
 *      'renderer' => [
 *          'nullMessage' => 'null',
 *          'trueMessage' => 'true',
 *          'falseMessage' => 'false',
 *      ]
 *      'layouts' => [
 *          'simple' => [
 *              'class' => LayoutSimple::class,
 *          ],
 *          'pattern' => [
 *              'class' => LayoutPattern::class,
 *              'pattern' => '{date:Y/m/d} [{level}] {logger} {location:file:line, class.function} {mdc:key} {mdc} {ndc}: {message} {ex}',
 *          ],
 *      ],
 *      'appenders' => [
 *          'stream' => [
 *              'class' => AppenderStream::class,
 *              'stream' => 'php://stdout',
 *              // pass useLock and useLockShortMessage true for enable lock
 *              'useLock' => false,
 *              'useLockShortMessage' => false,
 *              'minLevel' => Logger::TRACE,
 *              'maxLevel' => Logger::FATAL,
 *              'layout' => 'simple',
 *          ],
 *      ],
 *      'loggers' => [
 *          'logger' => [
 *              'appenders' => ['stream'],
 *              'addictive' => false,
 *              'minLevel' => Logger::DEBUG,
 *              'maxLevel' => Logger::FATAL,
 *          ],
 *      ],
 *      'root' => [
 *          'appenders' => ['stream'],
 *      ]
 * ]);
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
    private static $levelNameToLevel = [
        'OFF' => self::OFF,
        'FATAL' => self::FATAL,
        'ERROR' => self::ERROR,
        'WARN' => self::WARN,
        'INFO' => self::INFO,
        'DEBUG' => self::DEBUG,
        'TRACE' => self::TRACE,
        'ALL' => self::ALL,
    ];

    /**
     * Returns a Logger by name. If it does not exist, it will be created.
     *
     * @param string $name The logger name
     * @return Logger
     */
    public static function getLogger($name)
    {
        if (!self::$isConfigured) {
            static::configure();
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
            static::configure();
        }
        return self::$hierarchy->getRootLogger();
    }

    /**
     * @param null $configuration
     */
    public static function configure($configuration = null)
    {
        static::reset();
        if (is_string($configuration) && is_file($configuration)) {
            if (preg_match('/\.php$/', $configuration)) {
                /** @noinspection PhpIncludeInspection */
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
            static::configure();
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
        foreach (static::$levelNameToLevel as $levelName => $currentLevel) {
            if ($level >= $currentLevel) {
                return $levelName;
            }
        }
        return static::$levelNameToLevel[static::ALL];
    }

    /**
     * @param string $level
     * @return int
     */
    public static function getLevelByName($level)
    {
        $level = strtoupper($level);
        if (!isset(static::$levelNameToLevel[$level])) {
            return static::ALL;
        }
        return static::$levelNameToLevel[$level];
    }

    /*
     * Logger object description
     */
    private $name;
    /** @var Logger */
    private $parent;
    /** @var AppenderAbstract[] */
    private $appenders = [];
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

    public function addAppender(AppenderAbstract $appender)
    {
        if (!in_array($appender, $this->appenders)) {
            $this->appenders[] = $appender;
        }
    }

    public function removeAppender(AppenderAbstract $appender)
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

    public function trace($message, \Exception $throwable = null)
    {
        $this->log(static::TRACE, $message, $throwable);
    }

    public function debug($message, \Exception $throwable = null)
    {
        $this->log(static::DEBUG, $message, $throwable);
    }

    public function info($message, \Exception $throwable = null)
    {
        $this->log(static::INFO, $message, $throwable);
    }

    public function warn($message, \Exception $throwable = null)
    {
        $this->log(static::WARN, $message, $throwable);
    }

    public function error($message, \Exception $throwable = null)
    {
        $this->log(static::ERROR, $message, $throwable);
    }

    public function fatal($message, \Exception $throwable = null)
    {
        $this->log(static::FATAL, $message, $throwable);
    }

    public function log($level, $message, \Exception $throwable = null)
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
