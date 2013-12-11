<?php

class LoggerAppenderSyslog extends LoggerAppenderAbstract
{
    private $identifier;
    private $option;
    private $facility;

    public function __construct($identifier, $options, $facility)
    {
        $this->identifier = (string)$identifier;
        $this->option = self::parseOptions($options);
        $this->facility = self::parseOptions($facility);

    }

    public function write($priority, $message)
    {
        if (openlog($this->identifier, $this->option, $this->facility)) {
            syslog(self::getSyslogPriority($priority), $message);
            closelog();
        } else {
            $message = 'Error open syslog';
            switch (LoggerPolicy::getIOErrorPolicy()) {
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
                    throw new LoggerIOException($message);
            }
        }
    }

    public static function getSyslogPriority($level)
    {
        $level = (int)$level;
        switch (true) {
            case $level >= Logger::OFF:
            case $level >= Logger::FATAL:
                return LOG_ALERT;
                break;
            case $level >= Logger::ERROR:
                return LOG_ERR;
                break;
            case $level >= Logger::WARN:
                return LOG_WARNING;
                break;
            case $level >= Logger::INFO:
                return LOG_INFO;
                break;
            default:
                return LOG_DEBUG;
        }
    }

    public static function parseOptions($options)
    {
        if (is_string($options)) {
            $options = preg_split('/\|/', $options, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (is_array($options)) {
            $optionInteger = 0;
            foreach ($options as $opt) {
                if (is_string($opt) && defined($opt)) {
                    $opt = constant($opt);
                }
                if (is_int($opt)) {
                    $optionInteger |= $opt;
                } else {
                    $message = "Error parse syslog options";
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
                }
            }
            $options = $optionInteger;
        }
        if (is_int($options)) {
            return $options;
        } else {
            $message = "Invalid syslog options";
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
            return 0;
        }
    }
}
