<?php

namespace Mougrim\Logger\Layout\Pattern;

use Mougrim\Logger\Logger;

class PatternMemoryUsage implements PatternInterface
{
    private $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    private $precision = 2;
    private $roundTo;

    public function __construct($string)
    {
        if (strpos($string, ',') !== false) {
            $keys = preg_split('/,/', $string, -1, PREG_SPLIT_NO_EMPTY);
            if (isset($keys[0]) && is_numeric($keys[0])) {
                $this->precision = (int) $keys[0];
                unset($keys[0]);
            }
            if (isset($keys[1]) && is_numeric($keys[1])) {
                $this->roundTo = (int) $keys[1];
                unset($keys[1]);
            }
            if ($keys) {
                $this->units = array_values($keys);
                if ($this->roundTo !== null && count($this->units) === 1) {
                    $this->units[$this->roundTo] = $this->units[0];
                    unset($this->units[0]);
                }
            }
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
        $bytes = memory_get_usage(true);
        $base = log($bytes) / log(1024);
        $roundTo = $this->roundTo;
        if ($roundTo === null) {
            // auto
            $roundTo = min((int) floor($base), count($this->units) - 1);
        }

        return round(pow(1024, $base - $roundTo), $this->precision).$this->units[$roundTo];
    }
}
