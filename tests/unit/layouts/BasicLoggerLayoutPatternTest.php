<?php

spl_autoload_call('LoggerLayoutPattern');

class BasicLoggerLayoutPatternTest extends BaseLoggerTestCase
{
    /**
     * @param $format
     * @param $expected
     * @dataProvider formatProvider
     */
    public function testFormat($format, $expected)
    {
        LoggerNDC::clear();
        LoggerMDC::clear();
        LoggerNDC::push("ndc_context");
        LoggerMDC::put('key', 'value');
        $layout = new LoggerLayoutPattern($format);
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, 'hello world', new TestLoggerLayoutPatternException("test"));
        $this->assertEquals($expected, $message);
    }

    public function formatProvider()
    {
        global $argv;
        $argv = array(uniqid(), uniqid(), uniqid());
        $command = join(' ', $argv);
        return array(
            array(' ', ' ' . PHP_EOL),
            array('{date}', date('Y:m:d') . PHP_EOL),
            array('{date:Y:m:d}', date('Y:m:d') . PHP_EOL),
            array('{pid}', posix_getpid() . PHP_EOL),
            array('{level}', 'INFO' . PHP_EOL),
            array('{logger}', 'root' . PHP_EOL),
            array('{ex}', 'test' . PHP_EOL),
            array('{exception}', 'test' . PHP_EOL),
            array('{location}', __FILE__ . ':19' . PHP_EOL),
            array('{location:file}', __FILE__ . PHP_EOL),
            array('{location:file-line}', __FILE__ . '-19' . PHP_EOL),
            array('{location:class}', __CLASS__ . PHP_EOL),
            array('{location:class-function}', __CLASS__ . '-testFormat' . PHP_EOL),
            array('{global:somevar}', ($GLOBALS['somevar'] = uniqid()) . PHP_EOL),
            array('{global:some.var}', ($GLOBALS['some']['var'] = uniqid()) . PHP_EOL),
            array('{ndc}', 'ndc_context' . PHP_EOL),
            array('{mdc}', 'key=value' . PHP_EOL),
            array('{argv}', $command . PHP_EOL),
            array('{call:testCallableFunction}', 'testCallableFunction' . PHP_EOL),
            array('{call:TestCallableClass::testMethod}', 'TestCallableClass::testMethod' . PHP_EOL),
        );
    }

    public function testLoggerNDC()
    {
        LoggerNDC::clear();

        $layout = new LoggerLayoutPattern('{ndc}');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('' . PHP_EOL, $message);

        LoggerNDC::push("ndc");
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('ndc' . PHP_EOL, $message);
    }

    public function testLoggerMDC()
    {
        LoggerMDC::clear();
        $layout = new LoggerLayoutPattern('{mdc}');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('' . PHP_EOL, $message);

        LoggerMDC::put('foo', 'bar');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('foo=bar' . PHP_EOL, $message);

        LoggerMDC::clear();
        $layout = new LoggerLayoutPattern('{mdc:foo}');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('null' . PHP_EOL, $message);

        LoggerMDC::put('foo', 'bar');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('bar' . PHP_EOL, $message);
    }

    public function testRenderMessage()
    {
        $layout = new LoggerLayoutPattern('{message}');
        $this->assertEquals('string' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, 'string'));
        $this->assertEquals('123123' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, 123123));
        $this->assertEquals('123.123123123' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, 123.123123123));
        $this->assertEquals('false' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, false));
        $this->assertEquals('true' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, true));
        $this->assertEquals('null' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, null));
        $this->assertEquals('test' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, new TestLoggerLayoutPatternException("test")));
        $this->assertEquals(print_r(new stdClass(), 1) . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, new stdClass()));
    }

    public function testRenderException()
    {
        $layout = new LoggerLayoutPattern('{ex}');
        $this->assertEquals('' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, '', null));
        $this->assertEquals('test' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, '', new TestLoggerLayoutPatternException('test')));
    }

    public function testRenderFormat()
    {
        $layout = new LoggerLayoutPattern('{pid:%10s}');
        $this->assertEquals(sprintf('%10s', posix_getpid()) . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testErrorPatternGlobal()
    {
        $this->setExpectedException('LoggerException');
        new LoggerLayoutPattern('{global}');
    }

    public function testPatternGlobal()
    {
        $layout = new LoggerLayoutPattern('{global:foo}');
        $this->assertEquals('null' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testBacktraceMain()
    {
        $this->mockFunction('debug_backtrace', '', 'return array();');
        $layout = new LoggerLayoutPattern('{location:function}');
        $this->assertEquals('main' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryBytes()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1000;');
        $layout = new LoggerLayoutPattern('{memory}');
        $this->assertEquals('1000B' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryKBytes()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024;');
        $layout = new LoggerLayoutPattern('{memory}');
        $this->assertEquals('1KB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryMBytes()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024*1024;');
        $layout = new LoggerLayoutPattern('{memory}');
        $this->assertEquals('1MB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryGBytes()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024*1024*1024;');
        $layout = new LoggerLayoutPattern('{memory}');
        $this->assertEquals('1GB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryTBytes()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024*1024*1024*1024;');
        $layout = new LoggerLayoutPattern('{memory}');
        $this->assertEquals('1TB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryExtraBig()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024*1024*1024*1024*1024;');
        $layout = new LoggerLayoutPattern('{memory}');
        $this->assertEquals('1024TB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryPrecisionDefault()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024+128;');
        $layout = new LoggerLayoutPattern('{memory}');
        $this->assertEquals('1.13KB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryPrecisionNone()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024+128;');
        $layout = new LoggerLayoutPattern('{memory:0}');
        $this->assertEquals('1KB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryPrecisionTree()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024+128;');
        $layout = new LoggerLayoutPattern('{memory:3}');
        $this->assertEquals('1.125KB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryPrecisionLabel()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024*1024*1024*1024*1024+12*1024;');

        $layout = new LoggerLayoutPattern('{memory:0,bytes,kbytes,mbytes}');
        $this->assertEquals('1073741824mbytes' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));

        $layout = new LoggerLayoutPattern('{memory:2,bytes,kbytes,mbytes}');
        $this->assertEquals('1073741824.01mbytes' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }
}

function testCallableFunction()
{
    return __FUNCTION__;
}

class TestCallableClass
{
    public static function testMethod()
    {
        return __METHOD__;
    }
}

class TestLoggerLayoutPatternException extends Exception
{
    public function __toString()
    {
        return $this->getMessage();
    }
}