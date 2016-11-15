<?php

namespace Mougrim\Logger\functional;

use Mougrim\Logger\Appender\AppenderStream;

class LoggerWriterSteamTest extends \PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $path = '/tmp/tmp.log';
        $count = 100;
        $workers = 10;
        if (is_file($path)) {
            unlink($path);
        }
        $message = '';
        for ($i = 0; $i < 1000; ++$i) {
            $message .= uniqid();
        }
        $message .= PHP_EOL;

        $pids = [];
        for ($w = 0; $w < $workers; ++$w) {
            $pid = pcntl_fork();
            if (function_exists('pcntl_get_last_error')) {
                $error_number = pcntl_get_last_error();
                $error = "[{$error_number}] ".pcntl_strerror($error_number);
            } else {
                $error = var_export(error_get_last(), true);
            }
            $this->assertNotSame(-1, $pid, "Can't fork: ".$error);
            if ($pid) {
                $pids[] = $pid;
            } else {
                $writer = new AppenderStream($path);
                $writer->setUseLock(true);
                $writer->setUseLockShortMessage(true);
                for ($i = 0; $i < $count; ++$i) {
                    $writer->write(1, $message);
                }
                die();
            }
        }
        foreach ($pids as $p) {
            pcntl_waitpid($p, $status);
        }
        $this->assertSame(13001, strlen($message));
        $c = str_pad('', $count * $workers * strlen($message), $message);
        $this->assertSame($c, file_get_contents($path));
        if (is_file($path)) {
            unlink($path);
        }
    }
}
