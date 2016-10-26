<?php
namespace Mougrim\Logger;

use Mougrim\Logger\Appender\AppenderAbstract;

class LoggerTest extends BaseLoggerTestCase
{
    public function testConstruct()
    {
        $root = new Logger('root');
        $logger = new Logger('logger', $root);
        $this->assertEquals('logger', $logger->getName());
        $this->assertTrue($root === $logger->getParent());
        $this->assertEquals([], $logger->getAppenders());
        $this->assertTrue($logger->getAddictive());
    }

    public function testAppender()
    {
        $logger = new Logger('logger');
        /** @var $appender AppenderAbstract */
        $appender = $this->getMockForAbstractClass(AppenderAbstract::class);
        /** @var $appenderSecond AppenderAbstract */
        $appenderSecond = clone $appender;

        $this->assertEquals([], $logger->getAppenders());

        $logger->addAppender($appender);
        $this->assertEquals([$appender], $logger->getAppenders());
        $logger->addAppender($appender);
        $this->assertEquals([$appender], $logger->getAppenders());

        $logger->addAppender($appenderSecond);
        $this->assertEquals([$appender, $appenderSecond], $logger->getAppenders());
        $logger->addAppender($appenderSecond);
        $this->assertEquals([$appender, $appenderSecond], $logger->getAppenders());

        $logger->removeAppender($appenderSecond);
        $this->assertEquals([$appender], $logger->getAppenders());
        $logger->removeAppender($appender);
        $this->assertEquals([], $logger->getAppenders());
    }

    public function testAddictive()
    {
        $rootAppender = new AppenderTest();
        $root = new Logger('root');
        $root->addAppender($rootAppender);
        $logger = new Logger('logger', $root);
        $this->assertTrue($logger->getAddictive());
        $this->assertEquals($root, $logger->getParent());
        $logger->log(1, 'test1', $exFirst = new \Exception());
        $this->assertEquals([[1, 'test1']], $rootAppender->logs);

        $logger->setAddictive(false);
        $this->assertFalse($logger->getAddictive());
        $logger->log(2, 'test2', $exSecond = new \Exception());
        $this->assertEquals([[1, 'test1']], $rootAppender->logs);
    }

    public function testLogLevel()
    {
        $rootAppender = new AppenderTest();
        $root = new Logger('root');
        $root->addAppender($rootAppender);
        $logger = new Logger('logger', $root);
        $logger->trace('trace');
        $logger->debug('debug');
        $logger->info('info');
        $logger->warn('warn');
        $logger->error('error');
        $logger->fatal('fatal');

        $this->assertEquals([
            [Logger::TRACE, 'trace'],
            [Logger::DEBUG, 'debug'],
            [Logger::INFO, 'info'],
            [Logger::WARN, 'warn'],
            [Logger::ERROR, 'error'],
            [Logger::FATAL, 'fatal'],
        ], $rootAppender->logs);
    }

    public function levelProvider()
    {
        $cases = [];
        $reflection = new \ReflectionClass(Logger::class);
        foreach ($reflection->getConstants() as $name => $value) {
            $cases['Level ' . strtolower($name)] = [
                'level' => $value,
                'expected' => $name,
            ];
        }
        return $cases;
    }

    /**
     * @param $level
     * @param $expected
     * @dataProvider levelProvider
     */
    public function testGetLevelName($level, $expected)
    {
        $this->assertEquals(
            $expected,
            Logger::getLevelName($level)
        );
    }

    /**
     * @param $expected
     * @param $levelName
     * @dataProvider levelProvider
     */
    public function testGetLevelByName($expected, $levelName)
    {
        $this->assertEquals(
            $expected,
            Logger::getLevelByName($levelName)
        );
        strtolower($levelName);
        $this->assertEquals(
            $expected,
            Logger::getLevelByName($levelName)
        );
    }

    public function testMinLevel()
    {
        $logger = new Logger('root');
        $appender = new AppenderTest();
        $logger->addAppender($appender);

        $logger->log(Logger::INFO, 1);
        $this->assertEquals([
            [Logger::INFO, 1]
        ], $appender->logs);

        $logger->setMinLevel(Logger::INFO);
        $logger->log(Logger::INFO, 2);
        $logger->log(Logger::DEBUG, 3);
        $this->assertEquals([
            [Logger::INFO, 1],
            [Logger::INFO, 2]
        ], $appender->logs);
    }

    public function testMaxLevel()
    {
        $logger = new Logger('root');
        $appender = new AppenderTest();
        $logger->addAppender($appender);

        $logger->log(Logger::INFO, 1);
        $this->assertEquals([
            [Logger::INFO, 1]
        ], $appender->logs);

        $logger->setMaxLevel(Logger::INFO);
        $logger->log(Logger::INFO, 2);
        $logger->log(Logger::FATAL, 3);
        $logger->log(Logger::DEBUG, 4);
        $this->assertEquals([
            [Logger::INFO, 1],
            [Logger::INFO, 2],
            [Logger::DEBUG, 4]
        ], $appender->logs);
        return;
    }
}

class AppenderTest extends AppenderAbstract
{
    public $logs = [];

    public function write($level, $message)
    {
        $this->logs[] = func_get_args();
    }
}
