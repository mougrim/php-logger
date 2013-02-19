<?php

class LoggerConfigurator
{
    const LOGGER_RENDERER = 'renderer';
    const LOGGER_LAYOUTS = 'layouts';
    const LOGGER_APPENDERS = 'appenders';
    const LOGGER_LOGGERS = 'loggers';
    const LOGGER_ROOT = 'root';

    public function configure(LoggerHierarchy $hierarchy, array $config)
    {
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
                    throw new LoggerException('Appender invalid config');
                }
                $logger->addAppender($appender);
            }
            if (isset($config['addictive'])) {
                $logger->setAdditive($config['addictive']);
            }
        }
    }

    /**
     * @param LoggerHierarchy $hierarchy
     * @param $config
     * @return LoggerAppenderAbstract
     * @throws LoggerException
     */
    private function createAppender(LoggerHierarchy $hierarchy, $config)
    {
        if (isset($config['layout'])) {
            if (is_string($config['layout']))
                $config['layout'] = $hierarchy->getLayout($config['layout']);
            elseif (is_array($config['layout']))
                $config['layout'] = $this->createLayout($config['layout']); else
                throw new LoggerException('Invalid logger layout description');

        }
        return $this->createObject($config);
    }

    /**
     * @param $config
     * @return LoggerLayoutInterface
     */
    private function createLayout($config)
    {
        return $this->createObject($config);
    }

    private function createObject(array $config)
    {
        if (!isset($config['class']))
            throw new LoggerException('Key "class" is required');
        $reflection = new ReflectionClass($config['class']);
        unset($config['class']);

        $params = array();
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
        }
        $object = $reflection->newInstanceArgs($params);
        foreach ($config as $name => $value) {
            $method = 'set' . $name;
            if (method_exists($object, $method)) {
                call_user_func(array($object, $method), $value);
            }
        }
        return $object;
    }
}
