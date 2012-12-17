<?php

if (!defined('PHP_INT_MIN')) {
    define('PHP_INT_MIN', PHP_INT_MAX * -1 - 1);
}

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

    private $name;
    /** @var \Logger */
    private $parent;
    /** @var LoggerAppenderAbstract[] */
    private $appenders=array();
    /** @var bool */
    private $additive = true;

    public function __construct($name, Logger $parent = null)
    {
        $this->name = (string)$name;
        $this->parent = $parent;
    }

    public function addAppender(LoggerAppenderAbstract $appender)
    {
        if(!in_array($appender, $this->appenders)){
            $this->appenders[] = $appender;
        }
    }

    public function removeAppender(LoggerAppenderAbstract $appender)
    {
        $key = array_search($appender, $this->appenders);
        if($key){
            unset($this->appenders[$key]);
        }
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
        $this->log(self::TRACE, $message, $throwable);
    }

    public function info($message, Exception $throwable = null)
    {
        $this->log(self::TRACE, $message, $throwable);
    }

    public function warn($message, Exception $throwable = null)
    {
        $this->log(self::TRACE, $message, $throwable);
    }

    public function error($message, Exception $throwable = null)
    {
        $this->log(self::TRACE, $message, $throwable);
    }

    public function fatal($message, Exception $throwable = null)
    {
        $this->log(self::TRACE, $message, $throwable);
    }

    public function log($level, $message, Exception $throwable = null)
    {
        foreach($this->appenders as $appender){
            $appender->append($this, $level, $message, $throwable);
        }
        if($this->parent && $this->additive){
            $this->parent->log($level, $message, $throwable);
        }
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
                throw new LoggerException('О.o wtf?!');
        }
    }
}
