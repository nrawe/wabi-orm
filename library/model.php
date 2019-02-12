<?php
declare(strict_types=1);

/**
 * This subpackage provides helpers for working with models.
 */
namespace WabiORM;

/**
 * Uses reflection to create a new model instance.
 *
 * @internal
 * @subpackage WabiORM.Model
 * @param string $model
 * @param bool $withConstructor
 * @return object
 */
function create_model(string $model, bool $withConstructor = true): object {
    invariant(
        \class_exists($model),
        'create_model() is unable to create instances for non-existant models'
    );

    $rc = new \ReflectionClass($model);

    invariant(
        !$withConstructor || $rc->getConstructor()->getNumberOfRequiredParameters() === 0,
        'create_model() cannot create instances of models with required constructor arguments'
    );

    return $withConstructor
        ? $rc->newInstance()
        : $rc->newInstanceWithoutConstructor();
}

/**
 * Returns the table name based on the given model.
 * 
 * This is a rather naive implementation based on the general convention of
 * pluralisation.
 *
 * @internal 
 * @subpackage WabiORM.Model
 * @param string|object $model
 * @return string
 */
function model_default_table_name($model): string {
    $base = class_basename($model);

    return snake($base) . 's';
}

/**
 * Helper function which returns an array of meta data ("info") from a model.
 *
 * @internal
 * @subpackage WabiORM.Model
 * @param string|object $model
 * @return array
 */
function model_info($model): array {
    invariant(
        is_string($model) || is_object($model),
        'model_info() can only return data from a class reference or instance'
    );

    if (is_string($model)) {
        return model_info(create_model($model, false));
    }

    $override = model_override($model);

    return [
        'primaryKey' => $override('withPrimaryKey', 'id'),
        'tableName' => $override('withTableName', model_default_table_name($model)),
    ];
}

/**
 * Partial application which can be used to access an override from a model,
 * or return a default.
 *
 * @internal
 * @subpackage WabiORM.Model
 * @param object $model
 * @return callable
 */
function model_override(object $model): callable {
    return function (string $method, string $default) use ($model) {
        if (\method_exists($model, $method)) {
            return $model->$method();
        }

        return $default;
    };
}
