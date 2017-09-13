<?php

namespace Mougrim\Logger;

class LoggerTimerTest extends BaseLoggerTestCase
{
    /** @var float */
    private $start;
    /** @var float */
    private $time;
    /** @var float */
    private $end;
    /** @var Logger */
    private $root;
    /** @var Logger */
    private $logger;
    /** @var LoggerTimer */
    private $timer;

    protected function setUp()
    {
        parent::setUp();
        $this->start = (float) (string) microtime(1);
        $this->end = $this->start + 10;
        $this->time = $this->end - $this->start;
        $this->root = new Logger('root');
        $this->logger = new Logger('logger', $this->root);
        $this->timer = new LoggerTimer($this->logger, $this->start);
    }

    public function testRenderNone()
    {
        $this->assertSame('hello', $this->timer->render('hello', $this->end));
    }

    public function testRenderStart()
    {
        $this->assertSame("hello {$this->start}", $this->timer->render('hello {start}', $this->end));
    }

    public function testRenderEnd()
    {
        $this->assertSame("hello {$this->end}", $this->timer->render('hello {end}', $this->end));
    }

    public function testRenderTime()
    {
        $this->assertSame("hello {$this->time}", $this->timer->render('hello {time}', $this->end));
    }

    public function testTrace()
    {
        $end = $this->end;
        $this->redefineFunction('microtime', function () use ($end) {
            return $end;
        });
        /** @var \PHPUnit_Framework_MockObject_MockObject|Logger $mock */
        $mock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs(['logger'])
            ->setMethods(['log'])
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::TRACE), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->trace('hello {start} {end} {time}');
    }

    public function testDebug()
    {
        $end = $this->end;
        $this->redefineFunction('microtime', function () use ($end) {
            return $end;
        });
        /** @var \PHPUnit_Framework_MockObject_MockObject|Logger $mock */
        $mock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs(['logger'])
            ->setMethods(['log'])
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::DEBUG), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->debug('hello {start} {end} {time}');
    }

    public function testInfo()
    {
        $end = $this->end;
        $this->redefineFunction('microtime', function () use ($end) {
            return $end;
        });
        /** @var \PHPUnit_Framework_MockObject_MockObject|Logger $mock */
        $mock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs(['logger'])
            ->setMethods(['log'])
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::INFO), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->info('hello {start} {end} {time}');
    }

    public function testWarn()
    {
        $end = $this->end;
        $this->redefineFunction('microtime', function () use ($end) {
            return $end;
        });
        /** @var \PHPUnit_Framework_MockObject_MockObject|Logger $mock */
        $mock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs(['logger'])
            ->setMethods(['log'])
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::WARN), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->warn('hello {start} {end} {time}');
    }

    public function testError()
    {
        $end = $this->end;
        $this->redefineFunction('microtime', function () use ($end) {
            return $end;
        });
        /** @var \PHPUnit_Framework_MockObject_MockObject|Logger $mock */
        $mock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs(['logger'])
            ->setMethods(['log'])
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::ERROR), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->error('hello {start} {end} {time}');
    }

    public function testFatal()
    {
        $end = $this->end;
        $this->redefineFunction('microtime', function () use ($end) {
            return $end;
        });
        /** @var \PHPUnit_Framework_MockObject_MockObject|Logger $mock */
        $mock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs(['logger'])
            ->setMethods(['log'])
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::FATAL), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->fatal('hello {start} {end} {time}');
    }

    public function testLog()
    {
        $end = $this->end;
        $this->redefineFunction('microtime', function () use ($end) {
            return $end;
        });
        /** @var \PHPUnit_Framework_MockObject_MockObject|Logger $mock */
        $mock = $this->getMockBuilder(Logger::class)
            ->setConstructorArgs(['logger'])
            ->setMethods(['log'])
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::ALL), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->log(Logger::ALL, 'hello {start} {end} {time}');
    }
}
