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
        if (isset($config[static::LOGGER_POLICY]['ioError'])) {
            LoggerPolicy::setIoErrorPolicy($config[static::LOGGER_POLICY]['ioError']);
        }
        if (isset($config[static::LOGGER_POLICY]['configurationError'])) {
            LoggerPolicy::setConfigurationErrorPolicy($config[static::LOGGER_POLICY]['configurationError']);
        }
        if (isset($config[static::LOGGER_RENDERER])) {
            if (isset($config[static::LOGGER_RENDERER]['nullMessage'])) {
                LoggerRender::$nullMessage = (string) $config[static::LOGGER_RENDERER]['nullMessage'];
            }
            if (isset($config[static::LOGGER_RENDERER]['trueMessage'])) {
                LoggerRender::$trueMessage = (string) $config[static::LOGGER_RENDERER]['trueMessage'];
            }
            if (isset($config[static::LOGGER_RENDERER]['falseMessage'])) {
                LoggerRender::$falseMessage = (string) $config[static::LOGGER_RENDERER]['falseMessage'];
            }
        }
        if (isset($config[static::LOGGER_LAYOUTS])) {
            foreach ($config[static::LOGGER_LAYOUTS] as $layoutName => $layoutConfig) {
                $hierarchy->setLayout($layoutName, $this->createLayout($layoutConfig));
            }
        }
        if (isset($config[static::LOGGER_APPENDERS])) {
            foreach ($config[static::LOGGER_APPENDERS] as $appenderName => $appenderConfig) {
                $hierarchy->setAppender($appenderName, $this->createAppender($hierarchy, $appenderConfig));
            }
        }
        if (isset($config[static::LOGGER_LOGGERS])) {
            foreach ($config[static::LOGGER_LOGGERS] as $loggerName => $loggerConfig) {
                $logger = $hierarchy->getLogger($loggerName);
                $this->createLogger($logger, $hierarchy, $loggerConfig);
            }
        }
        if (isset($config[static::LOGGER_ROOT])) {
            $logger = $hierarchy->getRootLogger();
            $this->createLogger($logger, $hierarchy, $config[static::LOGGER_ROOT]);
        }
    }

    private function createLogger(Logger $logger, LoggerHierarchy $hierarchy, array $config)
    {
        if (isset($config['appenders'])) {
            foreach ($config['appenders'] as $appenderConfig) {
                if (is_string($appenderConfig)) {
                    $appender = $hierarchy->getAppender($appenderConfig);
                } elseif (is_array($appenderConfig)) {
                    $appender = $this->createAppender($hierarchy, $appenderConfig);
                } else {
                    LoggerPolicy::processConfigurationError('Appender invalid config');
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
     * @return AppenderAbstract
     *
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
                LoggerPolicy::processConfigurationError('Invalid logger layout description');
                unset($config['layout']);
            }
        }

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->createObject($config);
    }

    /**
     * @param $config
     *
     * @return LayoutInterface
     */
    private function createLayout($config)
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
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
            $method = 'set'.$name;
            if (method_exists($object, $method)) {
                $object->{$method}($value);
            }
        }

        return $object;
    }
}
