<?php

class LoggerConfiguratorTest extends PHPUnit_Framework_TestCase
{
    public function testConfigure()
    {
        $hierarchy = new LoggerHierarchy();
        $configurator = new LoggerConfigurator();
        $configurator->configure($hierarchy, array(
            'layouts' => array(
                'simple' => array(
                    'class' => 'LoggerLayoutSimple',
                ),
                'pattern' => array(
                    'class' => 'LoggerLayoutPattern',
                    'pattern' => '{date:Y/m/d} [{level}] {logger} {file}:{line} {class}:{function} {mdc:key} {mdc} {ndc}: {message} {ex}',
                ),
            ),
            'appenders' => array(
                'stream' => array(
                    'class' => 'LoggerAppenderStream',
                    'stream' => 'php://stdout',
                    'useLock' => true,
                    'useLockShortMessage' => false,
                    'minLevel' => 0,
                    'maxLevel' => PHP_INT_MAX,
                    'layout' => 'simple',
                ),
            ),
            'loggers' => array(
                'logger' => array(
                    'appenders' => array('stream'),
                    'addictive' => false,
                ),
            ),
            'root' => array(
                'appenders' => array('stream'),
            )
        ));
        $this->assertArrayHasKey('simple', $hierarchy->getLayoutMap());
        $this->assertArrayHasKey('pattern', $hierarchy->getLayoutMap());
        $this->assertArrayHasKey('stream', $hierarchy->getAppenderMap());
        $this->assertArrayHasKey('logger', $hierarchy->getLoggerMap());
    }
}
