<?php

namespace Mougrim\Logger\Layout;

use Mougrim\Logger\Layout\Pattern\PatternArgv;
use Mougrim\Logger\Layout\Pattern\PatternCallable;
use Mougrim\Logger\Layout\Pattern\PatternDate;
use Mougrim\Logger\Layout\Pattern\PatternException;
use Mougrim\Logger\Layout\Pattern\PatternGlobal;
use Mougrim\Logger\Layout\Pattern\PatternInterface;
use Mougrim\Logger\Layout\Pattern\PatternLevel;
use Mougrim\Logger\Layout\Pattern\PatternLocation;
use Mougrim\Logger\Layout\Pattern\PatternLogger;
use Mougrim\Logger\Layout\Pattern\PatternMDC;
use Mougrim\Logger\Layout\Pattern\PatternMemoryUsage;
use Mougrim\Logger\Layout\Pattern\PatternMessage;
use Mougrim\Logger\Layout\Pattern\PatternNDC;
use Mougrim\Logger\Layout\Pattern\PatternPid;
use Mougrim\Logger\Logger;

/**
 * Enterprise pattern layout.
 *
 * Available patterns:
 * - {date} or {date:<format>} standard date() format
 * - {pid} process id
 * - {level} level name, as example INFO
 * - {logger} logger name
 * - {message}
 * - {ex} or {exception}
 * - {location}
 * - {location:file:line}
 * - {location:class:function}
 * - {global:keypath} $GLOBAL variable. Example {global:page.num} returns $GLOBAL['page']['num']
 * - {mdc} or {mdc:key}
 * - {ndc}
 * - {argv}
 * - {callable:get_username} log return value of function <code>string function get_username(){}<code>
 * - {callable:Foo::bar} log return value of method <code>string Foo::bar(){}<code>
 * - {memory} formatted memory_get_usage(true) value with precision 2 and B, KB, MB, GB, TB
 * - {memory:3} set custom precision
 * - {memory:b,kb,mb,gb} set custom precision labels
 * - {memory:4,kb,mb} set custom precision and custom labels
 * - {memory:2,2} set custom precision and round to, rounds to: 0 - B, 1 - KB, 2 - MB, 3 - GB, 4 - TB
 * - {memory:2,2,mb} set custom precision, round to and custom label
 */
class LayoutPattern implements LayoutInterface
{
    const PATTERN_FULL = '{date:Y/m/d} [{level}] {logger} {location:file:line, class.function} {mdc:key} {mdc} {ndc}: {message} {ex}';
    const PATTERN_EXPRESSION = '/\{([a-z_-]+)(:([^}]+))?\}/i';

    private $pattern = self::PATTERN_FULL;
    private $patternMap = [
        'date' => PatternDate::class,
        'pid' => PatternPid::class,
        'level' => PatternLevel::class,
        'logger' => PatternLogger::class,
        'message' => PatternMessage::class,
        'ex' => PatternException::class,
        'exception' => PatternException::class,
        'location' => PatternLocation::class,
        'global' => PatternGlobal::class,
        'ndc' => PatternNDC::class,
        'mdc' => PatternMDC::class,
        'argv' => PatternArgv::class,
        'call' => PatternCallable::class,
        'memory' => PatternMemoryUsage::class,
    ];
    /** @var PatternInterface[] */
    private $patternMappers = [];

    public function __construct($pattern = null, array $additionalPatternMap = [])
    {
        if ($pattern) {
            $this->pattern = $pattern;
        }
        $this->patternMap = $additionalPatternMap + $this->patternMap;
        if (preg_match_all(static::PATTERN_EXPRESSION, $this->pattern, $matches)) {
            foreach ($matches[1] as $key => $patternName) {
                $fullMatch = $matches[0][$key];
                $rule = $matches[3][$key];
                if (isset($this->patternMap[$patternName])) {
                    $className = $this->patternMap[$patternName];
                    $this->patternMappers[$fullMatch] = new $className($rule);
                }
            }
        }
    }

    public function formatMessage(Logger $logger, $level, $message, \Exception $throwable = null)
    {
        $formatted = $this->pattern;
        foreach ($this->patternMappers as $pattern => $mapper) {
            $formatted = strtr(
                $formatted,
                [
                    $pattern => $mapper->render($logger, $level, $message, $throwable),
                ]
            );
        }

        return $formatted.PHP_EOL;
    }
}
