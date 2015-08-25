<?php
namespace Mougrim\Logger\integration;

use Mougrim\Logger\BaseLoggerTestCase;
use Mougrim\Logger\Appender\AppenderStream;
use Mougrim\Logger\LoggerHierarchy;

class LoggerHierarchyTest extends BaseLoggerTestCase
{
    public function testSimpleWrite()
    {
        $firstLog = '/tmp/first_log';
        $secondLog = '/tmp/second_log';
        if (is_file($firstLog)) unlink($firstLog);
        if (is_file($secondLog)) unlink($secondLog);
        $hierarchy = new LoggerHierarchy();
        $hierarchy->setAppender('first', $firstAppender = new AppenderStream($firstLog));
        $hierarchy->setAppender('second', $secondAppender = new AppenderStream($secondLog));
        $rootLogger = $hierarchy->getRootLogger();
        $rootLogger->addAppender($firstAppender);
        $rootLogger->addAppender($secondAppender);

        // test simple write
        $rootLogger->info($expected = uniqid());
        $this->assertEquals($expected, file_get_contents($firstLog));
        $this->assertEquals($expected, file_get_contents($secondLog));
    }

    public function testReopen()
    {
        $firstLog = '/tmp/first_log';
        $firstLogTmp = '/tmp/first_log_tmp';
        $secondLog = '/tmp/second_log';
        $secondLogTmp = '/tmp/second_log_tmp';
        if (is_file($firstLog)) unlink($firstLog);
        if (is_file($firstLogTmp)) unlink($firstLogTmp);
        if (is_file($secondLog)) unlink($secondLog);
        if (is_file($secondLogTmp)) unlink($secondLogTmp);
        $hierarchy = new LoggerHierarchy();
        $hierarchy->setAppender('first', $firstAppender = new AppenderStream($firstLog));
        $hierarchy->setAppender('second', $secondAppender = new AppenderStream($secondLog));
        $rootLogger = $hierarchy->getRootLogger();
        $rootLogger->addAppender($firstAppender);
        $rootLogger->addAppender($secondAppender);

        rename($firstLog, $firstLogTmp);
        rename($secondLog, $secondLog);

        $hierarchy->reopen();

        $rootLogger->info($expected = uniqid());

        $this->assertEquals($expected, file_get_contents($firstLog));
        $this->assertEquals($expected, file_get_contents($secondLog));
    }
}
