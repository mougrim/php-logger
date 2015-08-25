<?php
namespace Mougrim\Logger\Layout;

use Mougrim\Logger\BaseLoggerTestCase;
use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerException;
use Mougrim\Logger\LoggerMDC;
use Mougrim\Logger\LoggerNDC;

spl_autoload_call('LoggerLayoutPattern');

class LayoutPatternTest extends BaseLoggerTestCase
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
        $layout = new LayoutPattern($format);
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, 'hello world', new TestLoggerLayoutPatternException("test"));
        $this->assertEquals($expected, $message);
    }

    public function formatProvider()
    {
        global $argv;
        $argv = [uniqid(), uniqid(), uniqid()];
        $command = join(' ', $argv);
        return [
            [
                'format' => ' ',
                'expected' => ' ' . PHP_EOL,
            ],
            [
                'format' => '{date}',
                'expected' => date('Y:m:d') . PHP_EOL,
            ],
            [
                'format' => '{date:Y:m:d}',
                'expected' => date('Y:m:d') . PHP_EOL],
            [
                'format' => '{pid}',
                'expected' => posix_getpid() . PHP_EOL],
            [
                'format' => '{level}',
                'expected' => 'INFO' . PHP_EOL,
            ],
            [
                'format' => '{logger}',
                'expected' => 'root' . PHP_EOL,
            ],
            [
                'format' => '{ex}',
                'expected' => 'test' . PHP_EOL,
            ],
            [
                'format' => '{exception}',
                'expected' => 'test' . PHP_EOL,
            ],
            [
                'format' => '{location}',
                'expected' => __FILE__ . ':26' . PHP_EOL,
            ],
            [
                'format' => '{location:file}',
                'expected' => __FILE__ . PHP_EOL,
            ],
            [
                'format' => '{location:file-line}',
                'expected' => __FILE__ . '-26' . PHP_EOL,
            ],
            [
                'format' => '{location:class}',
                'expected' => __CLASS__ . PHP_EOL,
            ],
            [
                'format' => '{location:class-function}',
                'expected' => __CLASS__ . '-testFormat' . PHP_EOL,
            ],
            [
                'format' => '{global:somevar}',
                'expected' => ($GLOBALS['somevar'] = uniqid()) . PHP_EOL,
            ],
            [
                'format' => '{global:some.var}',
                'expected' => ($GLOBALS['some']['var'] = uniqid()) . PHP_EOL,
            ],
            [
                'format' => '{ndc}',
                'expected' => 'ndc_context' . PHP_EOL,
            ],
            [
                'format' => '{mdc}',
                'expected' => 'key=value' . PHP_EOL,
            ],
            [
                'format' => '{argv}',
                'expected' => $command . PHP_EOL,
            ],
            [
                'format' => '{call:Mougrim\Logger\Layout\testCallableFunction}',
                'expected' => 'Mougrim\Logger\Layout\testCallableFunction' . PHP_EOL,
            ],
            [
                'format' => '{call:' . TestCallableClass::class . '::testMethod}',
                'expected' => TestCallableClass::class . '::testMethod' . PHP_EOL,
            ],
        ];
    }

    public function testLoggerNDC()
    {
        LoggerNDC::clear();

        $layout = new LayoutPattern('{ndc}');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('' . PHP_EOL, $message);

        LoggerNDC::push("ndc");
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('ndc' . PHP_EOL, $message);
    }

    public function testLoggerMDC()
    {
        LoggerMDC::clear();
        $layout = new LayoutPattern('{mdc}');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('' . PHP_EOL, $message);

        LoggerMDC::put('foo', 'bar');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('foo=bar' . PHP_EOL, $message);

        LoggerMDC::clear();
        $layout = new LayoutPattern('{mdc:foo}');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('null' . PHP_EOL, $message);

        LoggerMDC::put('foo', 'bar');
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, '');
        $this->assertEquals('bar' . PHP_EOL, $message);
    }

    public function testRenderMessage()
    {
        $layout = new LayoutPattern('{message}');
        $this->assertEquals('string' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, 'string'));
        $this->assertEquals('123123' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, 123123));
        $this->assertEquals('123.123123123' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, 123.123123123));
        $this->assertEquals('false' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, false));
        $this->assertEquals('true' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, true));
        $this->assertEquals('null' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, null));
        $this->assertEquals('test' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, new TestLoggerLayoutPatternException("test")));
        $this->assertEquals(print_r(new \stdClass(), 1) . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, new \stdClass()));
    }

    public function testRenderException()
    {
        $layout = new LayoutPattern('{ex}');
        $this->assertEquals('' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, '', null));
        $this->assertEquals('test' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, '', new TestLoggerLayoutPatternException('test')));
    }

    public function testRenderFormat()
    {
        $layout = new LayoutPattern('{pid:%10s}');
        $this->assertEquals(sprintf('%10s', posix_getpid()) . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testErrorPatternGlobal()
    {
        $this->setExpectedException(LoggerException::class);
        new LayoutPattern('{global}');
    }

    public function testPatternGlobal()
    {
        $layout = new LayoutPattern('{global:foo}');
        $this->assertEquals('null' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testBacktraceMain()
    {
        $this->mockFunction('debug_backtrace', '', 'return [];');
        $layout = new LayoutPattern('{location:function}');
        $this->assertEquals('main' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryBytes()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1000;');
        $layout = new LayoutPattern('{memory}');
        $this->assertEquals('1000B' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryKBytes()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024;');
        $layout = new LayoutPattern('{memory}');
        $this->assertEquals('1KB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryMBytes()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024*1024;');
        $layout = new LayoutPattern('{memory}');
        $this->assertEquals('1MB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryGBytes()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024*1024*1024;');
        $layout = new LayoutPattern('{memory}');
        $this->assertEquals('1GB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryTBytes()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024*1024*1024*1024;');
        $layout = new LayoutPattern('{memory}');
        $this->assertEquals('1TB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryExtraBig()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024*1024*1024*1024*1024;');
        $layout = new LayoutPattern('{memory}');
        $this->assertEquals('1024TB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryPrecisionDefault()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024+128;');
        $layout = new LayoutPattern('{memory}');
        $this->assertEquals('1.13KB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryPrecisionNone()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024+128;');
        $layout = new LayoutPattern('{memory:0}');
        $this->assertEquals('1KB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryPrecisionTree()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024+128;');
        $layout = new LayoutPattern('{memory:3}');
        $this->assertEquals('1.125KB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemoryPrecisionLabel()
    {
        $this->mockFunction('memory_get_usage', '', 'return 1024*1024*1024*1024*1024+12*1024;');

        $layout = new LayoutPattern('{memory:0,bytes,kbytes,mbytes}');
        $this->assertEquals('1073741824mbytes' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));

        $layout = new LayoutPattern('{memory:2,bytes,kbytes,mbytes}');
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

class TestLoggerLayoutPatternException extends \Exception
{
    public function __toString()
    {
        return $this->getMessage();
    }
}
