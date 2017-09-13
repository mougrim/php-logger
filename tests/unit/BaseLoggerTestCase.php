<?php

namespace Mougrim\Logger;

use Mougrim\PhpunitSoftMocks\TestCase;

class BaseLoggerTestCase extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        LoggerPolicy::reset();
    }
}
