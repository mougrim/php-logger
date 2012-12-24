<?php

class LogTest extends PHPUnit_Framework_TestCase
{
    public function testLogSimple()
    {
        Logger::configure();
        Logger::getRootLogger()->info('hello world');
    }

    public function testLogStream()
    {
        $path = '/tmp/log.txt';
        if (is_file($path)) unlink($path);
        Logger::configure();
        $appender = new LoggerAppenderStream($path);
        $appender->setLayout(new LoggerLayoutSimple());
        Logger::getRootLogger()->addAppender($appender);
        Logger::getRootLogger()->info("hello world");
        $this->assertEquals("root [INFO] - hello world" . PHP_EOL, file_get_contents($path));
    }
}
