<?php

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
        $this->start = (float)(string)microtime(1);
        $this->end = $this->start + 10;
        $this->time = $this->end - $this->start;
        $this->root = new Logger('root');
        $this->logger = new Logger('logger', $this->root);
        $this->timer = new LoggerTimer($this->logger, $this->start);
    }

    public function testRenderNone()
    {
        $this->assertEquals("hello", $this->timer->render("hello", $this->end));
    }

    public function testRenderStart()
    {
        $this->assertEquals("hello {$this->start}", $this->timer->render("hello {start}", $this->end));
    }

    public function testRenderEnd()
    {
        $this->assertEquals("hello {$this->end}", $this->timer->render("hello {end}", $this->end));
    }

    public function testRenderTime()
    {
        $this->assertEquals("hello {$this->time}", $this->timer->render("hello {time}", $this->end));
    }

    public function testTrace()
    {
        $this->mockFunction('microtime', '', "return " . var_export($this->end, true) . ';');
        $mock = $this->getMockBuilder('Logger')
            ->setConstructorArgs(array('logger'))
            ->setMethods(array('log'))
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::TRACE), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->trace("hello {start} {end} {time}");
    }

    public function testDebug()
    {
        $this->mockFunction('microtime', '', "return " . var_export($this->end, true) . ';');
        $mock = $this->getMockBuilder('Logger')
            ->setConstructorArgs(array('logger'))
            ->setMethods(array('log'))
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::DEBUG), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->debug("hello {start} {end} {time}");
    }

    public function testInfo()
    {
        $this->mockFunction('microtime', '', "return " . var_export($this->end, true) . ';');
        $mock = $this->getMockBuilder('Logger')
            ->setConstructorArgs(array('logger'))
            ->setMethods(array('log'))
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::INFO), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->info("hello {start} {end} {time}");
    }

    public function testWarn()
    {
        $this->mockFunction('microtime', '', "return " . var_export($this->end, true) . ';');
        $mock = $this->getMockBuilder('Logger')
            ->setConstructorArgs(array('logger'))
            ->setMethods(array('log'))
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::WARN), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->warn("hello {start} {end} {time}");
    }

    public function testError()
    {
        $this->mockFunction('microtime', '', "return " . var_export($this->end, true) . ';');
        $mock = $this->getMockBuilder('Logger')
            ->setConstructorArgs(array('logger'))
            ->setMethods(array('log'))
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::ERROR), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->error("hello {start} {end} {time}");
    }

    public function testFatal()
    {
        $this->mockFunction('microtime', '', "return " . var_export($this->end, true) . ';');
        $mock = $this->getMockBuilder('Logger')
            ->setConstructorArgs(array('logger'))
            ->setMethods(array('log'))
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::FATAL), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->fatal("hello {start} {end} {time}");
    }

    public function testLog()
    {
        $this->mockFunction('microtime', '', "return " . var_export($this->end, true) . ';');
        $mock = $this->getMockBuilder('Logger')
            ->setConstructorArgs(array('logger'))
            ->setMethods(array('log'))
            ->getMock();
        $mock->expects($this->once())
            ->method('log')
            ->with($this->equalTo(Logger::ALL), $this->equalTo("hello {$this->start} {$this->end} {$this->time}"));
        $timer = new LoggerTimer($mock, $this->start);
        $timer->log(Logger::ALL, "hello {start} {end} {time}");
    }
}