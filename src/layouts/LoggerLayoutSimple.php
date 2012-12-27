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
        $formatted .= '- ' . LoggerRender::render($message);
        if ($throwable) {
            $formatted .= ' ' . LoggerRender::render($throwable);
        }
        return $formatted . PHP_EOL;
    }
}
