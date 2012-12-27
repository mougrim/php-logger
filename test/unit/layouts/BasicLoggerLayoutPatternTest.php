<?php

spl_autoload_call('LoggerLayoutPattern');

class BasicLoggerLayoutPatternTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param $format
     * @param $expected
     * @dataProvider formatProvider
     */
    public function testFormat($format, $expected)
    {
        $layout = new LoggerLayoutPattern($format);
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, 'hello world', new TestLoggerLayoutPatternException("test"));
        $this->assertEquals($expected, $message);
    }

    public function formatProvider()
    {
        LoggerNDC::clear();
        LoggerMDC::clear();
        LoggerNDC::push("ndc_context");
        LoggerMDC::put('key', 'value');
        return array(
            array(' ', ' ' . PHP_EOL),
            array('{date}', date('Y:m:d') . PHP_EOL),
            array('{date:Y:m:d}', date('Y:m:d') . PHP_EOL),
            array('{pid}', posix_getpid() . PHP_EOL),
            array('{level}', 'INFO' . PHP_EOL),
            array('{logger}', 'root' . PHP_EOL),
            array('{ex}', 'test' . PHP_EOL),
            array('{exception}', 'test' . PHP_EOL),
            array('{location}', __FILE__ . ':15' . PHP_EOL),
            array('{location:file}', __FILE__ . PHP_EOL),
            array('{location:file-line}', __FILE__ . '-15' . PHP_EOL),
            array('{location:class}', __CLASS__ . PHP_EOL),
            array('{location:class-function}', __CLASS__ . '-testFormat' . PHP_EOL),
            array('{global:somevar}', ($GLOBALS['somevar'] = uniqid()) . PHP_EOL),
            array('{global:some.var}', ($GLOBALS['some']['var'] = uniqid()) . PHP_EOL),
            array('{ndc}', 'ndc_context' . PHP_EOL),
            array('{mdc}', 'key=value' . PHP_EOL),
        );
    }

    public function testLoggerNDC()
    {
        LoggerNDC::clear();

        $layout = new LoggerLayoutPattern('{ndc}');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals(''.PHP_EOL, $message);

        LoggerNDC::push("ndc");
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('ndc'.PHP_EOL, $message);
    }

    public function testLoggerMDC()
    {
        LoggerMDC::clear();
        $layout = new LoggerLayoutPattern('{mdc}');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals(''.PHP_EOL, $message);

        LoggerMDC::put('foo','bar');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('foo=bar'.PHP_EOL, $message);

        LoggerMDC::clear();
        $layout = new LoggerLayoutPattern('{mdc:foo}');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals(''.PHP_EOL, $message);

        LoggerMDC::put('foo','bar');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('bar'.PHP_EOL, $message);
    }

    public function testRenderMessage()
    {
        $layout = new LoggerLayoutPattern('{message}');
        $this->assertEquals('string'.PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, 'string'));
        $this->assertEquals('123123'.PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, 123123));
        $this->assertEquals('123.123123123'.PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, 123.123123123));
        $this->assertEquals('false'.PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, false));
        $this->assertEquals('true'.PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, true));
        $this->assertEquals('null'.PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, null));
        $this->assertEquals('test'.PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, new TestLoggerLayoutPatternException("test")));
        $this->assertEquals(print_r(new stdClass(),1).PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, new stdClass()));
    }

    public function testRenderException()
    {
        $layout = new LoggerLayoutPattern('{ex}');
        $this->assertEquals(''.PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, '',null));
        $this->assertEquals('test'.PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, '',new TestLoggerLayoutPatternException('test')));
    }

    public function testRenderFormat()
    {
        $layout = new LoggerLayoutPattern('{pid:%10s}');
        $this->assertEquals(sprintf('%10s', posix_getpid()).PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testErrorPatternGlobal()
    {
        $this->setExpectedException('LoggerException');
        new LoggerLayoutPattern('{global}');
    }

    public function testPatternGlobal()
    {
        $layout = new LoggerLayoutPattern('{global:foo}');
        $this->assertEquals('null'.PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testBacktraceMain()
    {
        if(extension_loaded('runkit.so')){
            $this->markTestIncomplete('no runkit');
        }
        ini_set('runkit.internal_override', '1');
        runkit_function_copy('debug_backtrace', 'debug_backtrace_copy');
        runkit_function_redefine('debug_backtrace', '', 'return array();');
        $layout = new LoggerLayoutPattern('{location:function}');
        $this->assertEquals('main'.PHP_EOL,$layout->formatMessage(new Logger("root"), Logger::INFO, ''));
        runkit_function_remove('debug_backtrace');
        runkit_function_rename('debug_backtrace_copy', 'debug_backtrace');
    }
}

class TestLoggerLayoutPatternException extends Exception
{
    public function __toString()
    {
        return $this->getMessage();
    }
}