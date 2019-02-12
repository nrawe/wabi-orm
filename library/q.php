<?php
declare(strict_types=1);

/**
 * This subpackage provides a small, robust templating mechansim for binding
 * values in queries safely and with some smart shortcuts enabled.
 */
namespace WabiORM;

/**
 * Renders query templates, which hopefully reduce some of the plains of working
 * with plain SQL.
 * 
 * However, the template should be close enough to both plain SQL and existing
 * template languages that it's easy to identify intent.
 * 
 * @see docs/queries
 * @subpackage WabiORM.Q
 * @param string $template
 * @param array $data
 * @return array
 */
function q(string $template, array $data): array {
    $processors = processors();
    $getValue = array_accessor($data);

    return parse_bindings(
        $template, invoke_processor($processors, $getValue)
    );
}

/**
 * Returns whether the value or array of values are scalar.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param mixed $value
 * @return bool
 */
function are_values_scalar($value): bool {  
    if (\is_array($value)) {
        $filtered = filter(map($value, 'WabiORM\is_value_sane'));

        return count($filtered) === count($value);
    }

    return is_value_sane($value);
}

/**
 * Partial application which wraps access to the passed data array in safety
 * checks.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param array $data
 * @return callable
 */
function array_accessor(array $data): callable {
    return function (string $id) use ($data) {
        invariant(
            array_key_exists($id, $data),
            '"' . $id . '" does not exist in given data'
        );

        invariant(
            are_values_scalar($data[$id]),
            'value for "' . $id . '" must be a scalar ("' . gettype($data[$id]) . '" given)'
        );

        return $data[$id];
    };
}

/**
 * Strips away characters which are not valid for use as an identifier.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param string $expression
 * @return string
 */
function id_from_expr(string $expression): string {
    return preg_replace('/[^_a-zA-Z0-9]+/', '', $expression);
}

/**
 * Partial application which, when invoked, attempts to match a binding
 * expression to a processor.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param array $processors
 * @param callable $getValue
 * @return callable
 */
function invoke_processor(array $processors, callable $getValue): callable {
    return function (string $expr) use ($processors, $getValue) {
        $id = id_from_expr($expr);
        $processor = str_replace($id, 'id', $expr);

        invariant(
            array_key_exists($processor, $processors),
            'unable to process expression "' . $expr . '"'
        );

        return $processors[$processor]($id, $getValue($id));
    };
}

/**
 * Returns whether the value can be used inside of a binding.
 *
 * @param scalar|null $value
 * @return boolean
 */
function is_value_sane($value): bool {
    return is_scalar($value) || is_null($value);
}

/**
 * Returns the string indexes of the opening and closing tags for the next
 * binding.
 * 
 * This will also check for malformed tags.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param string $template
 * @return array
 */
function next_binding_position(string $template): array {
    $opens  = strpos($template, '{');

    if ($opens === false) {
        return [false, false];
    }

    $closes = strpos($template, '}', $opens + 1);

    invariant(
        $closes !== false,
        'missing closing "}" after character ' . $opens . ' of "' . $template . '"',
    );

    return [$opens, $closes];
}

/**
 * Parses binding expressions from the given template and returns the resultant
 * query and params.
 * 
 * Processing of the binding expressions themselves is delegated to the given
 * handler.
 * 
 * @internal
 * @subpackage WabiORM.Q
 * @param string $template
 * @param callable $handler
 * @return array
 */
function parse_bindings(string $template, callable $handler): array {
    $carry = $template;
    $final = '';
    $params = [];

    [$opens, $closes] = next_binding_position($carry);

    while ($opens !== false) {
        $final .= substr($carry, 0, $opens);
        $expr   = substr($carry, $opens + 1, $closes - $opens - 1);
        $carry  = substr($carry, $closes + 1);

        $result = $handler($expr);

        $final .= $result[0];
        array_push($params, ...$result[1]);

        [$opens, $closes] = next_binding_position($carry);
    }

    if ($carry) {
        $final .= $carry;
    }

    return [$final, $params];
}

/**
 * Returns an array which maps expressions to a processor function.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @return array
 */
function processors(): array {
    static $processors;

    if (! $processors) {
        $processors = [
            'id'   => direct_processor(),
            '*id'  => raw_processor(),
            '=id'  => equals_processor(false),
            '!id'  => equals_processor(true),
            '>id'  => greater_processor(false),
            '>=id' => greater_processor(true),
            '<id'  => lesser_processor(false),
            '<=id' => lesser_processor(true),
            '%id'  => like_processor(true, false),
            '%id%' => like_processor(true, true),
            'id%'  => like_processor(false, true), 
        ];
    }

    return $processors;
}

/**
 * Returns a processor for handling direct binding expressions.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @return callable
 */
function direct_processor(): callable {
    return function ($identifier, $value) {
        if (\is_array($value)) {
            $repeated = repeat('?, ', count($value));
            $trimmed = trim($repeated, ', ');

            return [$trimmed, $value];
        }

        return ['?', [$value]];
    };
}

/**
 * Returns a processor for handling equality binding expressions.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param boolean $negate
 * @return callable
 */
function equals_processor(bool $negate): callable {
    return function (string $identifier, $value) use ($negate) {
        if (is_array($value) && count($value) > 1) {
            return in_condition($identifier, $value, $negate);
        }

        if (is_array($value)) {
            return equals_condition($identifier, first($value), $negate);
        }

        return equals_condition($identifier, $value, $negate);
    };
}

/**
 * Returns a processor for handling greater than/equal to binding expressions.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param boolean $equalTo
 * @return callable
 */
function greater_processor(bool $equalTo): callable {
    return function (string $identifier, $value) use ($equalTo) {
        return compound_condition($identifier, $value, $equalTo ? '>=' : '>');
    };
}

/**
 * Returns a processor for handling lesser than/equal to binding expressions.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param boolean $equalTo
 * @return callable
 */
function lesser_processor(bool $equalTo): callable {
    return function (string $identifier, $value) use ($equalTo) {
        return compound_condition($identifier, $value, $equalTo ? '<=' : '<');
    };
}

/**
 * Returns a processor for handling text-matching binding expressions.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param boolean $before
 * @param boolean $after
 * @return callable
 */
function like_processor(bool $before, bool $after): callable {
    return function (string $identifier, $value) use ($before, $after) {
        $params = map((array)$value, function ($value) use ($before, $after) {
            if ($before) {
                $value = '%' . $value;
            }

            if ($after) {
                $value = $value . '%';
            }

            return $value;
        });

        return compound_condition($identifier, $params, 'like');
    };
}

/**
 * Returns a processor for handling raw binding expressions.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @return callable
 */
function raw_processor(): callable {
    return function ($identifier, $value) {
        if (\is_array($value)) {
            return [csvise($value), []];
        }

        return [$value, []];
    };
}

/**
 * Returns a potentially compound condition (i.e. `(cond1 or cond2)`).
 * 
 * The operator of the condition can be specified by the caller.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param string $field
 * @param mixed $values
 * @param string $op
 * @return array
 */
function compound_condition(string $field, $values, string $op): array {
    if (! is_array($values)) {
        $values = [$values];
    }

    $condition = '';

    foreach ($values as $value) {
        $condition .= $field . ' ' . $op . ' ? or ';
    }

    $condition = trim($condition, ' or ');
    if (count($values) > 1) {
        $condition = '(' . $condition . ')';
    }

    return [$condition, $values];
}

/**
 * Returns an equals or not equals condition for use in queries.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param string $field
 * @param boolean $negate
 * @return string
 */
function equals_condition(string $field, $value, bool $negate): array {
    return [$field . ($negate ? ' != ?' : ' = ?'), [$value]];
}

/**
 * Returns an 'in' or 'not in' condition for use in queries.
 *
 * @internal
 * @subpackage WabiORM.Q
 * @param string $field
 * @param array $value
 * @param integer $count
 * @return string
 */
function in_condition(string $field, array $value, bool $negate): array {
    $markers = \trim(repeat('?, ', count($value)), ', ');

    $expr = $field . ($negate ? ' not in (' : ' in (') . $markers . ')'; 

    return [$expr, $value];
}
