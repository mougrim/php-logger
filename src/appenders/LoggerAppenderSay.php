<?php

class LoggerAppenderSay extends LoggerAppenderAbstract
{
    public function write($level, $message)
    {
        system('say \''.escapeshellarg($message).'\'');
    }
}