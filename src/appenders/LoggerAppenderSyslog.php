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
            throw new LoggerException('Error open syslog');
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
            case $level >= Logger::DEBUG:
            case $level >= Logger::TRACE:
            case $level >= Logger::ALL:
                return LOG_DEBUG;
                break;
            default:
                throw new LoggerException('О.o wtf?!');
        }
    }

    public static function parseOptions($options)
    {
        if (is_string($options)) {
            $options = preg_split('/|/', $options, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (is_array($options)) {
            $optionInteger = 0;
            foreach ($options as $opt) {
                if (is_string($opt) && defined($opt)) {
                    $optionInteger |= constant($opt);
                } else if (is_int($opt)) {
                    $optionInteger |= $opt;
                } else {
                    throw new LoggerException("Error parse syslog options");
                }
            }
            $options = $optionInteger;
        }
        if (is_int($options)) {
            return $options;
        } else {
            throw new LoggerException("Invalid syslog options");
        }
    }
}
