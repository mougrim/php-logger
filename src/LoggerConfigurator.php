<?php
namespace Mougrim\Logger;

use Mougrim\Logger\Appender\AppenderAbstract;
use Mougrim\Logger\Layout\LayoutInterface;

class LoggerConfigurator
{
    const LOGGER_POLICY = 'policy';
    const LOGGER_RENDERER = 'renderer';
    const LOGGER_LAYOUTS = 'layouts';
    const LOGGER_APPENDERS = 'appenders';
    const LOGGER_LOGGERS = 'loggers';
    const LOGGER_ROOT = 'root';

    public function configure(LoggerHierarchy $hierarchy, array $config)
    {
        if (isset($config[self::LOGGER_POLICY]['ioError'])) {
            LoggerPolicy::setIoErrorPolicy($config[self::LOGGER_POLICY]['ioError']);
        }
        if (isset($config[self::LOGGER_POLICY]['configurationError'])) {
            LoggerPolicy::setConfigurationErrorPolicy($config[self::LOGGER_POLICY]['configurationError']);
        }
        if (isset($config[self::LOGGER_RENDERER])) {
            if (isset($config[self::LOGGER_RENDERER]['nullMessage'])) {
                LoggerRender::$nullMessage = (string)$config[self::LOGGER_RENDERER]['nullMessage'];
            }
            if (isset($config[self::LOGGER_RENDERER]['trueMessage'])) {
                LoggerRender::$trueMessage = (string)$config[self::LOGGER_RENDERER]['trueMessage'];
            }
            if (isset($config[self::LOGGER_RENDERER]['falseMessage'])) {
                LoggerRender::$falseMessage = (string)$config[self::LOGGER_RENDERER]['falseMessage'];
            }
        }
        if (isset($config[self::LOGGER_LAYOUTS])) {
            foreach ($config[self::LOGGER_LAYOUTS] as $layoutName => $layoutConfig) {
                $hierarchy->setLayout($layoutName, $this->createLayout($layoutConfig));
            }
        }
        if (isset($config[self::LOGGER_APPENDERS])) {
            foreach ($config[self::LOGGER_APPENDERS] as $appenderName => $appenderConfig) {
                $hierarchy->setAppender($appenderName, $this->createAppender($hierarchy, $appenderConfig));
            }
        }
        if (isset($config[self::LOGGER_LOGGERS])) {
            foreach ($config[self::LOGGER_LOGGERS] as $loggerName => $loggerConfig) {
                $logger = $hierarchy->getLogger($loggerName);
                $this->createLogger($logger, $hierarchy, $loggerConfig);
            }
        }
        if (isset($config[self::LOGGER_ROOT])) {
            $logger = $hierarchy->getRootLogger();
            $this->createLogger($logger, $hierarchy, $config[self::LOGGER_ROOT]);
        }
    }

    private function createLogger(Logger $logger, LoggerHierarchy $hierarchy, array $config)
    {
        if (isset($config['appenders'])) {
            foreach ($config['appenders'] as $appenderConfig) {
                if (is_string($appenderConfig)) {
                    $appender = $hierarchy->getAppender($appenderConfig);
                } else if (is_array($appenderConfig)) {
                    $appender = $this->createAppender($hierarchy, $appenderConfig);
                } else {
                    $message = 'Appender invalid config';
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
                    continue;
                }
                $logger->addAppender($appender);
            }
            if (isset($config['addictive'])) {
                $logger->setAddictive($config['addictive']);
            }
            if (isset($config['maxLevel'])) {
                $logger->setMaxLevel($config['maxLevel']);
            }
            if (isset($config['minLevel'])) {
                $logger->setMinLevel($config['minLevel']);
            }
        }
    }

    /**
     * @param LoggerHierarchy $hierarchy
     * @param $config
     *
*@return AppenderAbstract
     * @throws LoggerException
     */
    private function createAppender(LoggerHierarchy $hierarchy, $config)
    {
        if (isset($config['layout'])) {
            if (is_string($config['layout'])) {
                $config['layout'] = $hierarchy->getLayout($config['layout']);
            } elseif (is_array($config['layout'])) {
                $config['layout'] = $this->createLayout($config['layout']);
            } else {
                $message = 'Invalid logger layout description';
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
                unset($config['layout']);
            }
        }
        return $this->createObject($config);
    }

    /**
     * @param $config
     *
*@return LayoutInterface
     */
    private function createLayout($config)
    {
        return $this->createObject($config);
    }

    private function createObject(array $config)
    {
        if (!isset($config['class'])) {
            $message = 'Key "class" is required';
            switch (LoggerPolicy::getConfigurationErrorPolicy()) {
                case LoggerPolicy::POLICY_EXIT:
                    exit($message);
                case LoggerPolicy::POLICY_EXCEPTION:
                default:
                    throw new LoggerConfigurationException($message);
            }
        }
        $reflection = new \ReflectionClass($config['class']);
        unset($config['class']);

        $params = [];
        $constructor = $reflection->getConstructor();
        if ($constructor) {
            foreach ($constructor->getParameters() as $param) {
                $params[$param->getName()] = null;
                if ($param->isDefaultValueAvailable()) {
                    $params[$param->getName()] = $param->getDefaultValue();
                }
                if (isset($config[$param->getName()])) {
                    $params[$param->getName()] = $config[$param->getName()];
                    unset($config[$param->getName()]);
                }
            }
            $object = $reflection->newInstanceArgs($params);
        } else {
            $object = $reflection->newInstance();
        }
        foreach ($config as $name => $value) {
            $method = 'set' . $name;
            if (method_exists($object, $method)) {
                call_user_func([$object, $method], $value);
            }
        }
        return $object;
    }
}
