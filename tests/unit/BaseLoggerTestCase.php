<?php

namespace Mougrim\Logger;

use QA\SoftMocks;

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
        SoftMocks::restoreAll();
        parent::tearDown();
        LoggerPolicy::reset();
    }

    public function mockFunction($name, \Closure $function)
    {
        SoftMocks::redefineFunction($name, '', $function);
    }

    public function originalFunction($name)
    {
        SoftMocks::restoreFunction($name);
    }
}
