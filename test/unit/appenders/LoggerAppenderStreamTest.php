<?php

class LoggerAppenderStreamTest extends PHPUnit_Framework_TestCase
{
    public function testConstructIOException()
    {
        $this->setExpectedException('LoggerIOException');
        new LoggerAppenderStream('invalid://wefwef');
    }

    public function testNotUseLock()
    {
        if(extension_loaded('runkit.so')){
            $this->markTestIncomplete('no runkit');
        }
        ini_set('runkit.internal_override', '1');
        $error=null;
        runkit_function_copy('flock', 'flock_copy');
        runkit_function_redefine('flock', '', 'throw new BadFunctionCallException();');
        try{
            $appender = new LoggerAppenderStream('php://stdout');
            $appender->setUseLock(false);

            $appender->write(Logger::INFO, '');

        }catch (Exception $e){
            $error=$e;
        }
        runkit_function_remove('flock');
        runkit_function_rename('flock_copy', 'flock');
        if($error){
            throw $error;
        }
    }

    public function testNotUseLockShortMessage()
    {
        if(extension_loaded('runkit.so')){
            $this->markTestIncomplete('no runkit');
        }
        ini_set('runkit.internal_override', '1');
        $error=null;
        runkit_function_copy('flock', 'flock_copy');
        runkit_function_redefine('flock', '', 'throw new BadFunctionCallException();');
        try{
            $appender = new LoggerAppenderStream('php://stdout');
            $appender->setUseLock(true);
            $appender->setUseLockShortMessage(false);
            $appender->write(Logger::INFO, '');
            $appender->write(Logger::INFO, str_pad('', 4096, '1'));
        }catch (Exception $e){
            $error=$e;
        }
        runkit_function_remove('flock');
        runkit_function_rename('flock_copy', 'flock');
        if($error){
            throw $error;
        }
    }

    public function testUseLockShortMessage()
    {
        if(extension_loaded('runkit.so')){
            $this->markTestIncomplete('no runkit');
        }
        ini_set('runkit.internal_override', '1');
        $error=null;
        $GLOBALS['called']=false;
        runkit_function_copy('flock', 'flock_copy');
        runkit_function_redefine('flock', '', '$GLOBALS["called"]=true; return true;');
        try{
            $appender = new LoggerAppenderStream('/tmp/log.txt');
            $appender->setUseLock(true);
            $appender->setUseLockShortMessage(false);
            $appender->write(Logger::INFO, str_pad('', 4097, '1'));
        }catch (Exception $e){
            $error=$e;
        }
        runkit_function_remove('flock');
        runkit_function_rename('flock_copy', 'flock');
        if($error){
            throw $error;
        }

        $this->assertEquals(true, $GLOBALS['called']);
    }
}
