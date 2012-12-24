<?php

class LoggerHierarchy
{
    private $rootLogger;
    private $loggerMap;
    private $appenderMap;
    private $layoutMap;

    public function setRootLogger(Logger $rootLogger)
    {
        $this->rootLogger = $rootLogger;
    }

    public function getRootLogger()
    {
        if ($this->rootLogger)
            return $this->rootLogger;
        else
            return $this->rootLogger = new Logger('root');
    }

    public function getLogger($name)
    {
        if ($this->loggerMap[$name])
            return $this->loggerMap;
        else
            return $this->loggerMap[$name] = $this->createLogger($name);
    }

    public function createLogger($name)
    {
        return new Logger($name, $this->getRootLogger());
    }

    public function setAppender($name, LoggerAppenderAbstract $appender)
    {
        $this->appenderMap[$name] = $appender;
    }

    public function getAppender($name)
    {
        if ($this->appenderMap[$name])
            return $this->appenderMap[$name];
        else
            throw new LoggerException("Appender {$name} not found");

    }

    public function setLayout($name, LoggerLayoutInterface $layout)
    {
        $this->layoutMap[$name] = $layout;
    }

    public function getLayout($name)
    {
        if ($this->layoutMap[$name])
            return $this->layoutMap[$name];
        else
            throw new LoggerException("Layout {$name} not found");
    }

    public function getAppenderMap()
    {
        return $this->appenderMap;
    }

    public function getLayoutMap()
    {
        return $this->layoutMap;
    }

    public function getLoggerMap()
    {
        return $this->loggerMap;
    }
}
