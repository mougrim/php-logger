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
            if ($appender instanceof AppenderReopen) {
                $appender->reopen();
            }
        }
    }

    public function setRootLogger(Logger $rootLogger)
    {
        $this->rootLogger = $rootLogger;
    }

    public function getRootLogger()
    {
        if (isset($this->rootLogger))
            return $this->rootLogger;
        else
            return $this->rootLogger = new Logger('root');
    }

    public function getLogger($name)
    {
        if (isset($this->loggerMap[$name]))
            return $this->loggerMap[$name];
        else
            return $this->loggerMap[$name] = $this->createLogger($name);
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
        if (isset($this->appenderMap[$name])) {
            return $this->appenderMap[$name];
        } else {
            $message = "Appender {$name} not found";
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
            return new AppenderNull();
        }
    }

    public function setLayout($name, LayoutInterface $layout)
    {
        $this->layoutMap[$name] = $layout;
    }

    public function getLayout($name)
    {
        if (isset($this->layoutMap[$name])) {
            return $this->layoutMap[$name];
        } else {
            $message = "Layout {$name} not found";
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
            return new LayoutSimple();
        }
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
