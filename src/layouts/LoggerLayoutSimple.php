<?php

/**
 * Simple logger layout.
 * Format like as "loggerName [INFO] ndc_message mdc=context: message exception"
 */
class LoggerLayoutSimple implements LoggerLayoutInterface
{
    public function formatMessage(Logger $logger, $level, $message, Exception $throwable = null)
    {
        $formatted = $logger->getName()
            . ' [' . Logger::getLevelName($level) . '] ';
        if ($ndc = LoggerNDC::getStack()) {
            $formatted .= join(' ', $ndc) . ' ';
        }
        if ($mdc = LoggerMDC::getMap()) {
            foreach ($mdc as $key => $context) {
                $formatted .= $key . '=' . $context . ' ';
            }
        }
        $formatted .= '- ' . $this->render($message);
        if ($throwable) {
            $formatted .= ' ' . $this->render($throwable);
        }
        return $formatted . PHP_EOL;
    }

    private function render($message)
    {
        if(is_bool($message)){
            return $message ? 'true' : 'false';
        } else if (is_scalar($message)) {
            return (string)$message;
        } else if (is_object($message) && method_exists($message, '__toString')) {
            return $message;
        } else {
            return print_r($message, 1);
        }
    }
}
