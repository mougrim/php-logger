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

    public function mockFunction($funcName, $args, $expr)
    {
        if (!extension_loaded('runkit')) {
            $this->markTestIncomplete('Extension "runkit" is required');
        }
        ini_set('runkit.internal_override', '1');
        /** @noinspection PhpUndefinedFunctionInspection */
        runkit_function_rename($funcName, $funcName . '_copy');
        /** @noinspection PhpUndefinedFunctionInspection */
        runkit_function_add($funcName, $args, $expr);
        $this->mockFunctions[] = $funcName;
    }

    public function originalFunction($funcName)
    {
        if (in_array($funcName, $this->mockFunctions, true)) {
            /** @noinspection PhpUndefinedFunctionInspection */
            runkit_function_remove($funcName);
            /** @noinspection PhpUndefinedFunctionInspection */
            runkit_function_rename($funcName . '_copy', $funcName);
        }
    }
}
