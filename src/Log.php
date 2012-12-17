<?php

class Log
{
    /** @var Logger */
    private static $rootLogger;
    /** @var Logger[] */
    private static $loggers=array();
    /** @var bool */
    private static $isConfigured=false;

    /**
     * Returns a Logger by name. If it does not exist, it will be created.
     *
     * @param string $name The logger name
     * @return Logger
     */
    public static function getLogger($name)
    {
        if(self::$isConfigured){
            self::configure();
        }
        if(!isset(self::$loggers[$name])){
            self::$loggers[$name]=self::createLogger($name);
        }
    }

    /**
     * Returns the Root Logger.
     * @return LoggerRoot
     */
    public static function getRootLogger()
    {
        if(!self::$isConfigured){
            self::configure();
        }
        return self::$rootLogger;
    }

    /**
     * @param null $configuration
     */
    public static function configure($configuration = null)
    {
        self::$rootLogger=new Logger('root');
        self::$loggers=array();
        self::$isConfigured=false;
        self::$isConfigured=true;
    }

    private static function createLogger($name)
    {
        return new Logger($name, self::$rootLogger);
    }
}