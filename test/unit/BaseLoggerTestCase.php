<?php

class BaseLoggerTestCase extends PHPUnit_Framework_TestCase
{
    private $mockFunctions=array();

    public function tearDown(){
        foreach($this->mockFunctions as $func){
            $this->originalFunction($func);
        }
        parent::tearDown();
    }

    public function mockFunction($funcName, $args, $expr)
    {
        if(!extension_loaded('runkit')){
            $this->markTestIncomplete('Extension "runkit" is required');
        }
        ini_set('runkit.internal_override', '1');
        runkit_function_rename($funcName, $funcName.'_copy');
        runkit_function_add($funcName, $args, $expr);
        $this->mockFunctions[]=$funcName;
    }

    public function originalFunction($funcName)
    {
        if(in_array($funcName, $this->mockFunctions, true)){
            runkit_function_remove($funcName);
            runkit_function_rename($funcName.'_copy', $funcName);
        }
    }
}
