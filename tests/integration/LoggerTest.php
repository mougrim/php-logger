<?php
namespace Mougrim\Logger\integration;

use Mougrim\Logger\Appender\AppenderStream;
use Mougrim\Logger\BaseLoggerTestCase;
use Mougrim\Logger\Logger;

class LoggerTest extends BaseLoggerTestCase
{
    public function testReopen()
    {
        $f1 = '/tmp/log1';
        $f2 = '/tmp/log2';
        if (is_file($f1)) unlink($f1);
        if (is_file($f1 . '_tmp')) unlink($f1 . '_tmp');
        if (is_file($f2)) unlink($f2);
        if (is_file($f2 . '_tmp')) unlink($f2 . '_tmp');

        Logger::configure([
            'appenders' => [
                'stream1' => [
                    'class' => AppenderStream::class,
                    'stream' => $f1
                ],
                'stream2' => [
                    'class' => AppenderStream::class,
                    'stream' => $f2
                ],
            ],
            'root' => [
                'appenders' => ['stream1', 'stream2'],
            ]
        ]);
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

    public function testTest()
    {
        Logger::configure([
            'appenders' => [
                'stream' => [
                    'class' => AppenderStream::class,
                    'stream' => '/dev/null'
                ],
            ],
            'root' => [
                'appenders' => ['stream'],
            ]
        ]);

        $logger = Logger::getLogger('test');
        $start = microtime(1);
        for ($i = 1000; $i--;) {
            $logger->info('test');
        }
        $end = microtime(1);
        $this->assertLessThan(1.0, $end - $start, 'logger > root > stream');

        $logger = Logger::getRootLogger();
        $start = microtime(1);
        for ($i = 1000; $i--;) {
            $logger->info('test');
        }
        $end = microtime(1);
        $this->assertLessThan(1.0, $end - $start, 'root > stream');

        $appenders = Logger::getRootLogger()->getAppenders();
        $appender = $appenders[0];
        $start = microtime(1);
        for ($i = 1000; $i--;) {
            $appender->append($logger, Logger::INFO, 'test', null);
        }
        $end = microtime(1);
        $this->assertLessThan(1.0, $end - $start, 'stream');
    }
}
