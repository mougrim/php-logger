<?php

class LoggerLayoutSimple implements LoggerLayoutInterface
{
    public function formatMessage(Logger $logger, $level, $message, Exception $throwable = null)
    {
        $levelName = Logger::getLevelName($level);
        $message = $this->render($message);
        if($throwable){
            $message .= ' '.$this->render($throwable);
        }
        return "{$logger->getName()} [$levelName] - $message" . PHP_EOL;
    }

    private function render($message)
    {
        if(is_scalar($message)){
            return (string)$message;
        } else if(is_object($message) && method_exists($message, '__toString')){
            return $message;
        } else {
            return print_r($message, 1);
        }
    }
}
