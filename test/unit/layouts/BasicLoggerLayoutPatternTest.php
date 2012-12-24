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
        LoggerNDC::push("ndc_context");
        LoggerMDC::clear();
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
}

class TestLoggerLayoutPatternException extends Exception
{
    public function __toString()
    {
        return $this->getMessage();
    }
}