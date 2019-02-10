<?php
declare(strict_types=1);

/**
 * This subpackage provides a small, robust templating mechansim for binding
 * values in queries safely and with some smart shortcuts enabled.
 */
namespace WabiORM;

/**
 * Represents a regex that can be used to identify a value in the data array.
 * 
 * This is broadly in-line with PHPs name regex.
 * 
 * @internal
 * @subpackage WabiORM.Query
 */
const Q_IDENTIFIER_REGEX = '[_a-zA-Z0-9]+';

/**
 * Represents a regex that can be used to capture bindings.
 * 
 * @internal
 * @subpackage WabiORM.Query
 */
const Q_BINDING_REGEX = '/\{\{\s?(=|\!)(' . Q_IDENTIFIER_REGEX . ')\s?\}\}/';

/**
 * Represents a regex that can be used to match raw expressions.
 * 
 * @internal
 * @subpackage WabiORM.Query
 */
const Q_RAW_REGEX = '/\{\!\!\s?(' . Q_IDENTIFIER_REGEX . ')\s?\!\!\}/';

/**
 * Renders query template strings, which hopefully reduce some of the WTF's of
 * writing plain SQL.
 * 
 * However, the template should be close enough to both plain SQL and existing
 * template languages that it's easy to identify intent.
 * 
 * @subpackage WabiORM.Query
 * @param string $template
 * @param array $data
 * @return void
 */
function q(string $template, array $data): array {
    $processors = [
        process_raw_values($data),
        process_bindings($data),
    ];

    return array_reduce($processors, function ($carry, $processor) {
        return $processor(...$carry);
    }, [$template, []]);
}

/**
 * Partial application which can be used to replace raw expressions with their
 * value from the given data set.
 *
 * @internal
 * @subpackage WabiORM.Query
 * @param array $data
 * @return callable
 */
function process_raw_values(array $data): callable {
    $replacer = replace(Q_RAW_REGEX);

    return function (string $query, array $params) use ($data, $replacer) {
        $newQuery = $replacer($query, function ($matches) use ($data, $query) {
            [$expr, $identifier] = $matches;

            if (\array_key_exists($identifier, $data)) {
                return $data[$identifier];
            }

            throw new \InvalidArgumentException(
                "q(): invalid raw expression of '$expr' given in '$query';" .
                "'$identifier' was not supplied in the data"
            );
        });

        return [$newQuery, $params];
    };
}

/**
 * Partial application which can be used to replace binding expressions with
 * their value from the given set.
 *
 * @internal
 * @subpackage WabiORM.Query
 * @param array $data
 * @return callable
 */
function process_bindings(array $data): callable {
    $replacer = replace(Q_BINDING_REGEX);

    return function (string $query, array $params) use ($data, $replacer) {
        $query = $replacer($query, function ($matches) use ($data, &$params) {
            [$expr, $op, $identifier] = $matches;
            $value = $data[$identifier];
            
            if (\is_array($value)) {
                array_push($params, ...$value);

                return inCondition($identifier, count($value), $op === '!');
            }
            
            $params[] = $value;

            return equalsCondition($identifier, $op === '!');
        });

        return [$query, $params];
    };
}


/**
 * Partial application wrapper over preg_replace_callback for convenience.
 *
 * The returned callback can be invoked with a query string and replacement
 * handler.
 * 
 * @example
 * $replacer = replace(Q_RAW_REGEX);
 * $newQuery = $replacer($query, function ($matches) { return ''; });
 * 
 * @internal
 * @subpackage WabiORM.Query
 * @param string $regex
 * @return callable
 */
function replace(string $regex): callable {
    return function (string $query, callable $handler) use ($regex): string {
        return \preg_replace_callback($regex, $handler, $query);
    };
}

/**
 * Returns an 'in' or 'not in' condition for use in queries.
 *
 * @internal
 * @subpackage WabiORM.Query
 * @param integer $count
 * @return string
 */
function inCondition(string $field, int $placeholders, bool $negate): string {
    $markers = \trim(repeat('?, ', $placeholders), ', ');

    return $field . ($negate ? ' not in (' : ' in (') . $markers . ')';
}

/**
 * Returns an equals or not equals condition for use in queries.
 *
 * @internal
 * @subpackage WabiORM.Query
 * @param string $field
 * @param boolean $negate
 * @return string
 */
function equalsCondition(string $field, bool $negate): string {
    return $field . ($negate ? ' != ?' : ' = ?');
}
