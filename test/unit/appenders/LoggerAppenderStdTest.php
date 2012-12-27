<?php

class LoggerAppenderStdTest extends PHPUnit_Framework_TestCase
{
    public function testWriteDefault()
    {
        if(extension_loaded('runkit.so')){
            $this->markTestIncomplete('no runkit');
        }
        ini_set('runkit.internal_override', '1');
        $error=null;
        $GLOBALS['stream']=null;
        runkit_function_copy('fwrite', 'fwrite_copy');
        runkit_function_redefine('fwrite', '$a', '$GLOBALS["stream"]=$a;');
        try{
            $appender = new LoggerAppenderStd();
            $appender->write(Logger::INFO, 'test');
        }catch (Exception $e){
            $error=$e;
        }
        runkit_function_remove('fwrite');
        runkit_function_rename('fwrite_copy', 'fwrite');
        if($error){
            throw $error;
        }
        $this->assertTrue(STDOUT === $GLOBALS['stream']);
    }

    public function testWriteStdout()
    {
        if(extension_loaded('runkit.so')){
            $this->markTestIncomplete('no runkit');
        }
        ini_set('runkit.internal_override', '1');
        $error=null;
        $GLOBALS['stream']=null;
        runkit_function_copy('fwrite', 'fwrite_copy');
        runkit_function_redefine('fwrite', '$a', '$GLOBALS["stream"]=$a;');
        try{
            $appender = new LoggerAppenderStd();
            $appender->setStream('STDOUT');
            $appender->write(Logger::INFO, 'test');
        }catch (Exception $e){
            $error=$e;
        }
        runkit_function_remove('fwrite');
        runkit_function_rename('fwrite_copy', 'fwrite');
        if($error){
            throw $error;
        }
        $this->assertTrue(STDOUT === $GLOBALS['stream']);
    }

    public function testWriteStderr()
    {
        if(extension_loaded('runkit.so')){
            $this->markTestIncomplete('no runkit');
        }
        ini_set('runkit.internal_override', '1');
        $error=null;
        $GLOBALS['stream']=null;
        runkit_function_copy('fwrite', 'fwrite_copy');
        runkit_function_redefine('fwrite', '$a', '$GLOBALS["stream"]=$a;');
        try{
            $appender = new LoggerAppenderStd();
            $appender->setStream('STDERR');
            $appender->write(Logger::INFO, 'test');
        }catch (Exception $e){
            $error=$e;
        }
        runkit_function_remove('fwrite');
        runkit_function_rename('fwrite_copy', 'fwrite');
        if($error){
            throw $error;
        }
        $this->assertTrue(STDERR === $GLOBALS['stream']);
    }

    public function testInvalidStream()
    {
        $this->setExpectedException('LoggerIOException');
        $appender = new LoggerAppenderStd();
        $appender->setStream('ERROR');
    }
}
