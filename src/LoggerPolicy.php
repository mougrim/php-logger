<?php

class LoggerPolicy
{
    const POLICY_IGNORE = 'ignore';
    const POLICY_EXCEPTION = 'exception';
    const POLICY_TRIGGER_ERROR = 'trigger_error';
    const POLICY_EXIT = 'exit';

    private static $policyMap = array(
        self::POLICY_IGNORE => self::POLICY_IGNORE,
        self::POLICY_EXCEPTION => self::POLICY_EXCEPTION,
        self::POLICY_TRIGGER_ERROR => self::POLICY_TRIGGER_ERROR,
        self::POLICY_EXIT => self::POLICY_EXIT,
    );

    private static $ioErrorPolicy = self::POLICY_EXCEPTION;
    private static $configurationErrorPolicy = self::POLICY_EXCEPTION;


    public static function reset()
    {
        self::$ioErrorPolicy = self::POLICY_EXCEPTION;
        self::$configurationErrorPolicy = self::POLICY_EXCEPTION;
    }

    /**
     * @param string $policy
     * @throws LoggerConfigurationException
     */
    public static function setConfigurationErrorPolicy($policy)
    {
        if (in_array($policy, self::$policyMap, true)) {
            self::$configurationErrorPolicy = $policy;
        } else {
            throw new LoggerConfigurationException("Policy '{$policy}' not found");
        }
    }

    /**
     * @param string $policy
     * @throws LoggerConfigurationException
     */
    public static function setIoErrorPolicy($policy)
    {
        if (in_array($policy, self::$policyMap, true)) {
            self::$ioErrorPolicy = $policy;
        } else {
            throw new LoggerConfigurationException("Policy '{$policy}' not found");
        }
    }

    /**
     * @return string
     */
    public static function getIOErrorPolicy()
    {
        return self::$ioErrorPolicy;
    }

    /**
     * @return string
     */
    public static function getConfigurationErrorPolicy()
    {
        return self::$configurationErrorPolicy;
    }
}