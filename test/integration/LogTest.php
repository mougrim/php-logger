<?php

class LogTest extends PHPUnit_Framework_TestCase
{
    public function testLogSimple()
    {
        Log::configure();
        Log::getRootLogger()->info('hello world');
    }

    public function testLogStream()
    {
        $path = '/tmp/log.txt';
        if(is_file($path)) unlink($path);
        Log::configure();
        $appender = new LoggerAppenderStream($path);
        $appender->setLayout(new LoggerLayoutSimple());
        Log::getRootLogger()->addAppender($appender);
        Log::getRootLogger()->info("hello world");
        $this->assertEquals("root [TRACE] - hello world".PHP_EOL, file_get_contents($path));
    }
}
