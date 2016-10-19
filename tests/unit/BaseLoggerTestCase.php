<?php
namespace Mougrim\Logger;

class BaseLoggerTestCase extends \PHPUnit_Framework_TestCase
{
    private $mockFunctions;

    protected function setUp()
    {
        $this->mockFunctions = [];
        parent::setUp();
    }


    protected function tearDown()
    {
        foreach ($this->mockFunctions as $func) {
            $this->originalFunction($func);
        }
        parent::tearDown();
        LoggerPolicy::reset();
    }

    public function mockFunction($name, \Closure $function)
    {
        if (extension_loaded('uopz')) {
            /** @noinspection PhpUndefinedFunctionInspection */
            uopz_set_return($name, $function, true);
            $this->mockFunctions[] = $name;
        } elseif (extension_loaded('runkit')) {
            ini_set('runkit.internal_override', '1');
            // copy original function only once
            if (!function_exists($name . '_original')) {
                /** @noinspection PhpUndefinedFunctionInspection */
                runkit_function_rename($name, $name . '_original');
                $this->mockFunctions[] = $name;
            } else {
                /** @noinspection PhpUndefinedFunctionInspection */
                runkit_function_remove($name);
            }
            /** @noinspection PhpUndefinedFunctionInspection */
            runkit_function_add($name, $function);
        } else {
            $this->markTestIncomplete('Extension "uopz" or "runkit" is required');
        }
    }

    public function originalFunction($name)
    {
        if (in_array($name, $this->mockFunctions, true)) {
            if (extension_loaded('uopz')) {
                /** @noinspection PhpUndefinedFunctionInspection */
                uopz_unset_return($name);
            } elseif (extension_loaded('runkit')) {
                /** @noinspection PhpUndefinedFunctionInspection */
                runkit_function_remove($name);
                /** @noinspection PhpUndefinedFunctionInspection */
                runkit_function_rename($name . '_original', $name);
            }
        }
    }
}
