<?php

namespace Mougrim\Logger;

class LoggerPolicy
{
    const POLICY_IGNORE = 'ignore';
    const POLICY_EXCEPTION = 'exception';
    const POLICY_TRIGGER_WARN = 'trigger_warn';
    const POLICY_TRIGGER_ERROR = 'trigger_error';
    const POLICY_EXIT = 'exit';

    private static $policyMap = [
        self::POLICY_IGNORE => self::POLICY_IGNORE,
        self::POLICY_EXCEPTION => self::POLICY_EXCEPTION,
        self::POLICY_TRIGGER_WARN => self::POLICY_TRIGGER_WARN,
        self::POLICY_TRIGGER_ERROR => self::POLICY_TRIGGER_ERROR,
        self::POLICY_EXIT => self::POLICY_EXIT,
    ];

    private static $ioErrorPolicy = self::POLICY_EXCEPTION;
    private static $configurationErrorPolicy = self::POLICY_EXCEPTION;

    public static function reset()
    {
        self::$ioErrorPolicy = self::POLICY_EXCEPTION;
        self::$configurationErrorPolicy = self::POLICY_EXCEPTION;
    }

    /**
     * @param string $policy
     *
     * @throws LoggerConfigurationException
     */
    public static function setConfigurationErrorPolicy($policy)
    {
        if (!in_array($policy, self::$policyMap, true)) {
            throw new LoggerConfigurationException("Policy '{$policy}' not found");
        }
        self::$configurationErrorPolicy = $policy;
    }

    /**
     * @return string
     */
    public static function getConfigurationErrorPolicy()
    {
        return self::$configurationErrorPolicy;
    }

    public static function processConfigurationError($message)
    {
        switch (self::getConfigurationErrorPolicy()) {
            case self::POLICY_IGNORE:
                return;
            case self::POLICY_TRIGGER_WARN:
                trigger_error($message, E_USER_WARNING);

                return;
            case self::POLICY_TRIGGER_ERROR:
                trigger_error($message, E_USER_ERROR);

                return;
            case self::POLICY_EXIT:
                exit($message);
            case self::POLICY_EXCEPTION:
            default:
                throw new LoggerConfigurationException($message);
        }
    }

    /**
     * @param string $policy
     *
     * @throws LoggerConfigurationException
     */
    public static function setIoErrorPolicy($policy)
    {
        if (!in_array($policy, self::$policyMap, true)) {
            throw new LoggerConfigurationException("Policy '{$policy}' not found");
        }
        self::$ioErrorPolicy = $policy;
    }

    /**
     * @return string
     */
    public static function getIOErrorPolicy()
    {
        return self::$ioErrorPolicy;
    }

    public static function processIOError($message)
    {
        switch (self::getIOErrorPolicy()) {
            case self::POLICY_IGNORE:
                return;
            case self::POLICY_TRIGGER_WARN:
                trigger_error($message, E_USER_WARNING);

                return;
            case self::POLICY_TRIGGER_ERROR:
                trigger_error($message, E_USER_ERROR);

                return;
            case self::POLICY_EXIT:
                exit($message);
            case self::POLICY_EXCEPTION:
            default:
                throw new LoggerIOException($message);
        }
    }
}
