<?php
require_once dirname(__DIR__) .'/vendor/badoo/soft-mocks/src/bootstrap.php';
\QA\SoftMocks::setPhpunitPath('/vendor/phpunit/');

require_once \QA\SoftMocks::rewrite(dirname(__DIR__) . '/vendor/autoload.php');
require_once \QA\SoftMocks::rewrite(__DIR__ . '/unit/BaseLoggerTestCase.php');
