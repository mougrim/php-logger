<?php
namespace Mougrim\Logger\Appender;

class AppenderNull extends AppenderAbstract
{
    public function write($level, $message)
    {
    }
}
