<?php

class LoggerAppenderSay extends LoggerAppenderAbstract
{
    public function write($level, $message)
    {
        return system('say \''.escapeshellarg($message).'\'');
    }
}