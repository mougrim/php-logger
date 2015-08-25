<?php
namespace Mougrim\Logger\Layout;

use Mougrim\Logger\Logger;
use Mougrim\Logger\LoggerMDC;
use Mougrim\Logger\LoggerNDC;
use Mougrim\Logger\LoggerRender;

/**
 * Simple logger layout.
 * Format like as "loggerName [INFO] ndc_message mdc=context: message exception"
 */
class LayoutSimple implements LayoutInterface
{
    public function formatMessage(Logger $logger, $level, $message, \Exception $throwable = null)
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
