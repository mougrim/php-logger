<?php

class LoggerAppenderNull extends LoggerAppenderAbstract
{
    public function write($level, $message)
    {
    }
}