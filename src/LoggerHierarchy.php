<?php
namespace Mougrim\Logger;

use Mougrim\Logger\Appender\AppenderAbstract;
use Mougrim\Logger\Appender\AppenderNull;
use Mougrim\Logger\Appender\AppenderReopen;
use Mougrim\Logger\Layout\LayoutInterface;
use Mougrim\Logger\Layout\LayoutSimple;

class LoggerHierarchy
{
    private $rootLogger;
    private $loggerMap = [];
    private $appenderMap = [];
    private $layoutMap = [];

    /**
     * Reopen appenders, what has support reopen log stream
     */
    public function reopen()
    {
        foreach ($this->appenderMap as $appender) {
            if (!$appender instanceof AppenderReopen) {
                continue;
            }
            $appender->reopen();
        }
    }

    public function setRootLogger(Logger $rootLogger)
    {
        $this->rootLogger = $rootLogger;
    }

    public function getRootLogger()
    {
        if ($this->rootLogger === null) {
            return $this->rootLogger = new Logger('root');
        }
        return $this->rootLogger;
    }

    public function getLogger($name)
    {
        if (!isset($this->loggerMap[$name])) {
            return $this->loggerMap[$name] = $this->createLogger($name);
        }
        return $this->loggerMap[$name];
    }

    public function createLogger($name)
    {
        return new Logger($name, $this->getRootLogger());
    }

    public function setAppender($name, AppenderAbstract $appender)
    {
        $this->appenderMap[$name] = $appender;
    }

    /**
     * @param $name
     *
     * @return AppenderAbstract
     * @throws LoggerConfigurationException
     */
    public function getAppender($name)
    {
        if (!isset($this->appenderMap[$name])) {
            LoggerPolicy::processConfigurationError("Appender {$name} not found");
            return new AppenderNull();
        }
        return $this->appenderMap[$name];
    }

    public function setLayout($name, LayoutInterface $layout)
    {
        $this->layoutMap[$name] = $layout;
    }

    public function getLayout($name)
    {
        if (!isset($this->layoutMap[$name])) {
            LoggerPolicy::processConfigurationError("Layout {$name} not found");
            return new LayoutSimple();
        }
        return $this->layoutMap[$name];
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
