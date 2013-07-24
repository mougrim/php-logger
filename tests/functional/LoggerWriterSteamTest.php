<?php

class LoggerWriterSteamTest extends PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $path = "/tmp/tmp.log";
        $count = 100;
        $workers = 10;
        if (is_file($path)) {
            unlink($path);
        }
        $message = '';
        for ($i = 0; $i < 1000; $i++) $message .= uniqid();
        $message .= PHP_EOL;

        $pids = array();
        $start = microtime(1);
        for ($w = 0; $w < $workers; $w++) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                $this->markTestIncomplete('could not fork');
            } else if ($pid) {
                $pids[] = $pid;
            } else {
                $writer = new LoggerAppenderStream($path);
                for ($i = 0; $i < $count; $i++) $writer->write(1, $message);
                die();
            }
        }
        foreach ($pids as $p) {
            pcntl_waitpid($p, $status);
        }
        $this->lessThan(0.2, microtime(1) - $start);
        $this->assertTrue(strlen($message) === 13001);
        $c = str_pad("", $count * $workers * strlen($message), $message);
        sleep(1);
        file_put_contents($path . '.ex', $c);
        $this->assertEquals($c, file_get_contents($path));
        if (is_file($path)) {
            unlink($path);
        }
    }
}
