<?php
namespace Mougrim\Logger\Layout;

use Mougrim\Logger\Logger;

interface LayoutInterface
{
    /**
     * @param Logger $logger
     * @param int $level
     * @param string $message
     * @param \Exception|null $throwable
     * @return mixed
     */
    public function formatMessage(Logger $logger, $level, $message, \Exception $throwable = null);
}
