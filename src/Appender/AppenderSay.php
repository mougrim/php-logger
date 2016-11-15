<?php

namespace Mougrim\Logger\Appender;

class AppenderSay extends AppenderAbstract
{
    public function write($level, $message)
    {
        return system('say '.escapeshellarg($message));
    }
}
