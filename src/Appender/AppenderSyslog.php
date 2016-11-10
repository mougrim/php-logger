<?php
namespace Mougrim\Logger\Appender;

use Mougrim\Logger\Logger;
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
        if (!openlog($this->identifier, $this->option, $this->facility)) {
            LoggerPolicy::processIOError('Error open syslog');
            return;
        }
        syslog(static::getSyslogPriority($priority), $message);
        closelog();
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
            foreach ($options as $option) {
                if (is_string($option) && defined($option)) {
                    $option = constant($option);
                }
                if (!is_int($option)) {
                    LoggerPolicy::processConfigurationError(
                        'Error parse syslog options, option: ' . var_export($option, true)
                    );
                    continue;
                }
                $optionInteger |= $option;
            }
            $options = $optionInteger;
        }
        if (!is_int($options)) {
            LoggerPolicy::processConfigurationError('Invalid syslog options: ' . var_export($options, true));
            return 0;
        }
        return $options;
    }
}
