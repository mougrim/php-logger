<?php
namespace Mougrim\Logger\Layout;

use Mougrim\Logger\BaseLoggerTestCase;
use Mougrim\Logger\Layout\Pattern\PatternInterface;
use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerException;
use Mougrim\Logger\LoggerMDC;
use Mougrim\Logger\LoggerNDC;

class LayoutPatternTest extends BaseLoggerTestCase
{
    /**
     * @param       $format
     * @param       $expected
     * @param array $additionalPatternMap
     *
     * @dataProvider formatProvider
     */
    public function testFormat($format, $expected, array $additionalPatternMap)
    {
        LoggerNDC::clear();
        LoggerMDC::clear();
        LoggerNDC::push("ndc_context");
        LoggerMDC::put('key', 'value');
        $layout = new LayoutPattern($format, $additionalPatternMap);
        $message = $layout->formatMessage(new Logger("root"), Logger::INFO, 'hello world', new TestLayoutPatternException("test"));
        $this->assertEquals($expected, $message);
    }

    public function formatProvider()
    {
        global $argv;
        $argv = [uniqid(), uniqid(), uniqid()];
        $command = join(' ', $argv);
        return [
            'Format space' => [
                'format' => ' ',
                'expected' => ' ' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format date' => [
                'format' => '{date}',
                'expected' => date('Y:m:d') . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format date format' => [
                'format' => '{date:Y:m:d}',
                'expected' => date('Y:m:d') . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format pid' => [
                'format' => '{pid}',
                'expected' => posix_getpid() . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format level' => [
                'format' => '{level}',
                'expected' => 'INFO' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format logger' => [
                'format' => '{logger}',
                'expected' => 'root' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format exception short' => [
                'format' => '{ex}',
                'expected' => 'test' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format exception' => [
                'format' => '{exception}',
                'expected' => 'test' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format location' => [
                'format' => '{location}',
                'expected' => __FILE__ . ':27' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format location file' => [
                'format' => '{location:file}',
                'expected' => __FILE__ . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format location file-line' => [
                'format' => '{location:file-line}',
                'expected' => __FILE__ . '-27' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format location class' => [
                'format' => '{location:class}',
                'expected' => __CLASS__ . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format location class-function' => [
                'format' => '{location:class-function}',
                'expected' => __CLASS__ . '-testFormat' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format global var' => [
                'format' => '{global:somevar}',
                'expected' => ($GLOBALS['somevar'] = uniqid()) . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format global nesting var' => [
                'format' => '{global:some.var}',
                'expected' => ($GLOBALS['some']['var'] = uniqid()) . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format ndc' => [
                'format' => '{ndc}',
                'expected' => 'ndc_context' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format mdc' => [
                'format' => '{mdc}',
                'expected' => 'key=value' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format argv' => [
                'format' => '{argv}',
                'expected' => $command . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format function' => [
                'format' => '{call:Mougrim\Logger\Layout\testCallableFunction}',
                'expected' => 'Mougrim\Logger\Layout\testCallableFunction' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format callback' => [
                'format' => '{call:' . TestCallableClass::class . '::testMethod}',
                'expected' => TestCallableClass::class . '::testMethod' . PHP_EOL,
                'additionalPatternMap' => [],
            ],
            'Format additional pattern' => [
                'format' => '{additional_pattern}',
                'expected' => 'additional_pattern' . PHP_EOL,
                'additionalPatternMap' => [
                    'additional_pattern' => TestAdditionalPattern::class,
                ],
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
        $this->assertEquals('test' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, new TestLayoutPatternException("test")));
        $this->assertEquals(print_r(new \stdClass(), 1) . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, new \stdClass()));
    }

    public function testRenderException()
    {
        $layout = new LayoutPattern('{ex}');
        $this->assertEquals('' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, '', null));
        $this->assertEquals('test' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, '', new TestLayoutPatternException('test')));
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

    public function testMemorySuffix()
    {
        $this->mockFunction('memory_get_usage', '', 'return 12*1024;');
        $layout = new LayoutPattern('{memory:0,2}');
        $this->assertEquals('0MB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));

        $this->mockFunction('memory_get_usage', '', 'return 12*1024*1024;');
        $layout = new LayoutPattern('{memory:0,2}');
        $this->assertEquals('12MB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));

        $this->mockFunction('memory_get_usage', '', 'return 12*1024*1024*1024;');
        $layout = new LayoutPattern('{memory:0,2}');
        $this->assertEquals('12288MB' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
    }

    public function testMemorySuffixLabel()
    {
        $this->mockFunction('memory_get_usage', '', 'return 12*1024*1024;');
        $layout = new LayoutPattern('{memory:0,2,mbytes}');
        $this->assertEquals('12mbytes' . PHP_EOL, $layout->formatMessage(new Logger("root"), Logger::INFO, ''));
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

class TestLayoutPatternException extends \Exception
{
    public function __toString()
    {
        return $this->getMessage();
    }
}

class TestAdditionalPattern implements PatternInterface
{
    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        return 'additional_pattern';
    }
}
