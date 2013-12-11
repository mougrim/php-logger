<?php

class LoggerLayoutSimpleTest extends BaseLoggerTestCase
{
    public function setUp()
    {
        LoggerNDC::clear();
        LoggerMDC::clear();
        parent::setUp();
    }

    public function testLoggerName()
    {
        $logger = new Logger("testLogger");
        $layout = new LoggerLayoutSimple();
        $this->assertEquals('testLogger [INFO] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));
    }

    public function testLevel()
    {
        $logger = new Logger("testLogger");
        $layout = new LoggerLayoutSimple();

        $this->assertEquals('testLogger [OFF] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::OFF, "test message"));
        $this->assertEquals('testLogger [FATAL] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::FATAL, "test message"));
        $this->assertEquals('testLogger [ERROR] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::ERROR, "test message"));
        $this->assertEquals('testLogger [WARN] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::WARN, "test message"));
        $this->assertEquals('testLogger [INFO] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));
        $this->assertEquals('testLogger [DEBUG] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::DEBUG, "test message"));
        $this->assertEquals('testLogger [TRACE] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::TRACE, "test message"));
        $this->assertEquals('testLogger [ALL] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::ALL, "test message"));

    }

    public function testLoggerNDC()
    {
        $logger = new Logger("testLogger");
        $layout = new LoggerLayoutSimple();

        $this->assertEquals('testLogger [INFO] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));

        LoggerNDC::push("stack1");
        $this->assertEquals('testLogger [INFO] stack1 - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));

        LoggerNDC::push("stack2");
        $this->assertEquals('testLogger [INFO] stack1 stack2 - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));

        LoggerNDC::pop();
        $this->assertEquals('testLogger [INFO] stack1 - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));

        LoggerNDC::pop();
        $this->assertEquals('testLogger [INFO] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));
    }

    public function testLoggerMDC()
    {
        $logger = new Logger("testLogger");
        $layout = new LoggerLayoutSimple();

        $this->assertEquals('testLogger [INFO] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));

        LoggerMDC::put("foo", "bar");
        $this->assertEquals('testLogger [INFO] foo=bar - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));

        LoggerMDC::put("zip", "tar");
        $this->assertEquals('testLogger [INFO] foo=bar zip=tar - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));

        LoggerMDC::remove("foo");
        $this->assertEquals('testLogger [INFO] zip=tar - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));

        LoggerMDC::remove("zip");
        $this->assertEquals('testLogger [INFO] - test message' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test message"));
    }

    public function testRenderScalar()
    {
        $logger = new Logger("testLogger");
        $layout = new LoggerLayoutSimple();

        $this->assertEquals('testLogger [INFO] - string' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "string"));

        $this->assertEquals('testLogger [INFO] - 0.013412123' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, 0.013412123));

        $this->assertEquals('testLogger [INFO] - 123123123123' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, 123123123123));

        $this->assertEquals('testLogger [INFO] - false' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, false));
    }

    public function testRenderNullAndBool()
    {
        LoggerRender::$nullMessage = '-';
        LoggerRender::$trueMessage = '1';
        LoggerRender::$falseMessage = '-1';
        $logger = new Logger("testLogger");
        $layout = new LoggerLayoutSimple();

        $this->assertEquals('testLogger [INFO] - -' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, null));

        $this->assertEquals('testLogger [INFO] - 1' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, true));

        $this->assertEquals('testLogger [INFO] - -1' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, false));
    }

    public function renderObjectToString()
    {
        $logger = new Logger("testLogger");
        $layout = new LoggerLayoutSimple();

        $this->assertEquals('testLogger [INFO] - test exception' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, new LoggerLayoutSimpleTestException("test exception")));
    }

    public function testRenderObject()
    {
        $testObject = new stdClass();

        $logger = new Logger("testLogger");
        $layout = new LoggerLayoutSimple();

        $this->assertEquals('testLogger [INFO] - ' . print_r($testObject, 1) . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, $testObject));
    }

    public function testRenderMessageAndException()
    {
        $logger = new Logger("testLogger");
        $layout = new LoggerLayoutSimple();
        $ex = new LoggerLayoutSimpleTestException("exception");
        $this->assertEquals('testLogger [INFO] - test exception' . PHP_EOL,
            $layout->formatMessage($logger, Logger::INFO, "test", $ex));
    }
}

class LoggerLayoutSimpleTestException extends Exception
{
    public function __toString()
    {
        return $this->getMessage();
    }
}