<?php

use Badoo\SoftMocks;

SoftMocks::setRewriteInternal(true);
require_once SoftMocks::rewrite(__DIR__.'/unit/BaseLoggerTestCase.php');
