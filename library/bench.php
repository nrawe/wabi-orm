<?php

/**
 * This subpackage provides functions for testing performance of PHP code.
 * 
 * These utilities are defined to provide rough estimates only.
 */
namespace WabiORM;

/**
 * Executes the given callback the specified amount of times and returns the
 * average execution time.
 * 
 * This currently does not run in strict mode.
 *
 * @param integer $times
 * @param callable $fn
 * @return void
 */
function average_execution_time(int $times, callable $fn): float {
    $results = [];

    for ($i = 0; $i < $times; $i++) {
        $results[] = execution_time($fn);
    }

    return array_sum($results) / $times;
}

/**
 * Returns the microseconds that elapsed during execution of given callback.
 *
 * @param callable $fn
 * @return float
 */
function execution_time(callable $fn): float {
    $start = microtime(true);
    $fn();
    $end = microtime(true);

    return $end - $start;
}

/**
 * Returns a human readable memory size.
 *
 * Borrowed from UBench.
 * 
 * @see https://github.com/devster/ubench
 * @param int $size
 * @param string $format The format to display (printf format)
 * @param int $round
 * @return  string
 */
function readable_size(int $size, string $format = null, int $round = 3): int
{
    $mod = 1024;

    if (is_null($format)) {
        $format = '%.2f%s';
    }

    $units = explode(' ','B Kb Mb Gb Tb');
    
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }

    if (0 === $i) {
        $format = preg_replace('/(%.[\d]+f)/', '%d', $format);
    }

    return sprintf($format, round($size, $round), $units[$i]);
}

/**
 * Returns a human readable elapsed time.
 *
 * Borrowed from UBench.
 * 
 * @see https://github.com/devster/ubench
 * @param float $microtime
 * @param string $format The format to display (printf format)
 * @param int $round
 * @return string
 */
function readable_elapsed_time(float $microtime, string $format = null, int $round = 3): string
{
    if (is_null($format)) {
        $format = '%.3f%s';
    }

    if ($microtime >= 1) {
        $unit = 's';
        $time = round($microtime, $round);
    } else {
        $unit = 'ms';
        $time = round($microtime*1000);
        $format = preg_replace('/(%.[\d]+f)/', '%d', $format);
    }

    return sprintf($format, $time, $unit);
}

/**
 * Runs the given callback for the specified number of seconds and returns the
 * amount of times execution occurred.
 * 
 * This is not a fully precise measurement but should give an overall scale of
 * execution performance in the context of an HTTP Request.
 *
 * @param integer $seconds
 * @param callable $fn
 * @return integer
 */
function run_for(int $seconds, callable $fn): int {
    $times = 0;

    // Try and get us to the next nearest whole second
    usleep(1 - fmod(microtime(true), 1));

    $until = time() + $seconds;

    while (time() < $until) {
        $fn();
        $times++;
    }

    return $times;
}
