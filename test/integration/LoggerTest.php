<?php

namespace integration;

use Logger;
use PHPUnit_Framework_TestCase;

class LoggerTest extends PHPUnit_Framework_TestCase
{
    public function testReopen()
    {
        $f1 = '/tmp/log1';
        $f2 = '/tmp/log2';
        if (is_file($f1)) unlink($f1);
        if (is_file($f1 . '_tmp')) unlink($f1 . '_tmp');
        if (is_file($f2)) unlink($f2);
        if (is_file($f2 . '_tmp')) unlink($f2 . '_tmp');

        Logger::configure(array(
            'appenders' => array(
                'stream1' => array(
                    'class' => 'LoggerAppenderStream',
                    'stream' => $f1
                ),
                'stream2' => array(
                    'class' => 'LoggerAppenderStream',
                    'stream' => $f2
                ),
            ),
            'root' => array(
                'appenders' => array('stream1', 'stream2'),
            )
        ));
        Logger::getRootLogger()->info($expected = uniqid());
        $this->assertEquals($expected, file_get_contents($f1));
        $this->assertEquals($expected, file_get_contents($f2));

        rename($f1, $f1 . '_tmp');
        rename($f2, $f2 . '_tmp');

        Logger::reopen();
        $this->assertEquals('', file_get_contents($f1));
        $this->assertEquals('', file_get_contents($f2));
        Logger::getRootLogger()->info($expected = uniqid());
        $this->assertEquals($expected, file_get_contents($f1));
        $this->assertEquals($expected, file_get_contents($f2));
    }
}