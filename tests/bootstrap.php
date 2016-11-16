<?php

require_once dirname(__DIR__).'/vendor/badoo/soft-mocks/src/bootstrap.php';
\QA\SoftMocks::setRewriteInternal(true);
\QA\SoftMocks::setPhpunitPath('/vendor/phpunit/');

require_once \QA\SoftMocks::rewrite(dirname(__DIR__).'/vendor/autoload.php');
require_once \QA\SoftMocks::rewrite(__DIR__.'/unit/BaseLoggerTestCase.php');
require_once \QA\SoftMocks::rewrite(__DIR__.'/CodeCoverageFilter.php');
require_once \QA\SoftMocks::rewrite(__DIR__.'/PHPUnitCommand.php');
// inject into phpunit
class_exists(\PHPUnit_Util_Fileloader::class);
\QA\SoftMocks::init();
