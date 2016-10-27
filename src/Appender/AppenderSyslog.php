<?php
namespace Mougrim\Logger\Appender;

use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerConfigurationException;
use Mougrim\Logger\LoggerIOException;
use Mougrim\Logger\LoggerPolicy;

class AppenderSyslog extends AppenderAbstract
{
    private static $levelToPriority = [
        Logger::OFF => LOG_ALERT,
        Logger::FATAL => LOG_ALERT,
        Logger::ERROR => LOG_ERR,
        Logger::WARN => LOG_WARNING,
        Logger::INFO => LOG_INFO,
    ];
    private $identifier;
    private $option;
    private $facility;

    public function __construct($identifier, $options, $facility)
    {
        $this->identifier = (string)$identifier;
        $this->option = static::parseOptions($options);
        $this->facility = static::parseOptions($facility);

    }

    public function write($priority, $message)
    {
        if (openlog($this->identifier, $this->option, $this->facility)) {
            syslog(static::getSyslogPriority($priority), $message);
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
        foreach (static::$levelToPriority as $currentLevel => $priority) {
            if ($level >= $currentLevel) {
                return $priority;
            }
        }
        return LOG_DEBUG;
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
