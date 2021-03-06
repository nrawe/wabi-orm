<?php
declare(strict_types=1);

/**
 * This subpackage provides helper functions used by other parts of the
 * framework.
 */
namespace WabiORM;

/**
 * Get the class "basename" of the given object / class.
 * 
 * This is taken from the Illuminate source code.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @see illuminate/support::helpers::class_basename()
 * @param string|object $class
 * @return string
 */
function class_basename($class) {
    $class = is_object($class) ? get_class($class) : $class;
    
    return basename(str_replace('\\', '/', $class));
}

/**
 * Joins the given items as a string of comma separated values.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @param array $items
 * @return string
 */
function csvise(array $items): string {
    return trim(join(', ', $items), ', ');
}

/**
 * Wrapper around array_filter for brevity.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @param array $target
 * @param callable $discrimiator
 * @return array
 */
function filter(array $target, callable $discrimiator = null): array {
    // Bonkers, but a PHP warning with strict types if not specified this way.
    if ($discrimiator) {
        return \array_filter($target, $discrimiator);
    }

    return \array_filter($target);
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
 * @param ConnectResultInterface $result
 * @return array
 */
function hydrate(string $model, ConnectResultInterface $result): array {
    return $result->statement()->fetchAll(\PDO::FETCH_CLASS, $model);
}

/**
 * Throws an exception in the event that the given condition is false.
 * 
 * The thrown exception will contain the given message.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @param boolean $condition
 * @param string $message
 * @return void
 */
function invariant(bool $condition, string $message): void {
    if (! $condition) {
        throw new \RuntimeException('Invariant Violation: ' . $message);
    }
}

/**
 * Attempts to return the ID of the last inserted record from the result.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @param ConnectResultInterface $result
 * @return void
 */
function last_insert_id(ConnectResultInterface $result): string {
    return $result->connection()->lastInsertId();
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
 * Wrapper around array_map for consistency and brevity.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @param array $target
 * @param callable $fn
 * @return array
 */
function map(array $target, callable $fn): array {
    return array_map($fn, $target);
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

/**
 * Marshalls the given value into an array.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @param [type] $value
 * @return array
 */
function to_array($value): array {
    return is_array($value) ? $value : [$value];
}

/**
 * Returns whether the given query executed successfully.
 *
 * @internal
 * @subpackage WabiORM.Utilities
 * @param ConnectResultInterface $result
 * @return boolean
 */
function was_execution_successful(ConnectResultInterface $result): bool {
    return $result->statement()->errorCode() === '00000';
}
