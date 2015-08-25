<?php
/**
 * @author Mougrim <rinat@mougrim.ru>
 */

namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;

class PatternMemoryUsage implements PatternInterface
{
    private $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    private $precision = 2;

    public function __construct($string)
    {
        if (strpos($string, ',') !== false) {
            $keys = preg_split('/,/', $string, -1, PREG_SPLIT_NO_EMPTY);
            if (is_numeric($keys[0])) {
                $this->precision = (int) $keys[0];
                unset($keys[0]);
            }
            $this->units = array_values($keys);
        } elseif (is_numeric($string)) {
            $this->precision = (int) $string;
        }
    }

    /**
     * @param Logger     $logger
     * @param            $level
     * @param            $message
     * @param \Exception $throwable
     *
     * @return string
     */
    public function render(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        $bytes  = memory_get_usage(true);
        $base   = log($bytes) / log(1024);
        $suffix = min((int) floor($base), count($this->units) - 1);

        return round(pow(1024, $base - $suffix), $this->precision) . $this->units[$suffix];
    }
}
