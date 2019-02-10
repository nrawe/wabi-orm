<?php
declare(strict_types=1);

namespace WabiORM;

/**
 * Get the class "basename" of the given object / class.
 * 
 * This is taken from the Illuminate source code.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @see illuminate/support::helpers::class_basename()
 * @param  string|object  $class
 * @return string
 */
function class_basename($class) {
    $class = is_object($class) ? get_class($class) : $class;
    
    return basename(str_replace('\\', '/', $class));
}

/**
 * Returns the first element of the array, or null if the array is empty.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @param array $set
 * @return mixed|null
 */
function first(array $set) {
    $key = array_key_first($set);

    if (is_null($key)) {
        return null;
    }

    return $set[$key];
}

/**
 * Hydrates object instances from the given PDO results.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @param string $model
 * @param \PDOStatement $stmt
 * @return array
 */
function hydrate(string $model, \PDOStatement $stmt): array {
    return $stmt->fetchAll(\PDO::FETCH_CLASS, $model);
}

/**
 * Convert the given string to lower-case.
 * 
 * This is taken from the Illuminate source code.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @see illuminate/support::Str::lower()
 * @param  string  $value
 * @return string
 */
function lower($value) {
    return mb_strtolower($value, 'UTF-8');
}

/**
 * Repeats a string a given number of times.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @param string $pattern
 * @param integer $times
 * @return string
 */
function repeat(string $pattern, int $times): string {
    $carry = '';

    for ($i = 0; $i < $times; $i++) {
        $carry .= $pattern;
    }

    return $carry;
}

/**
 * Convert a string to snake case.
 *
 * This is taken from the Illuminate source code.
 * 
 * @internal
 * @subpackage WabiORM.Utilities
 * @see illuminate/support::Str::snake()
 * @param  string  $value
 * @param  string  $delimiter
 * @return string
 */
function snake($value, $delimiter = '_') {
    $key = $value;

    if (! ctype_lower($value)) {
        $value = preg_replace('/\s+/u', '', ucwords($value));
        $value = lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
    }

    return $value;
}


