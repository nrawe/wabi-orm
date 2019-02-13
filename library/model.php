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
 * Returns the data currently contained in the model.
 * 
 * This can be configured per-model through the presence of a
 * `withDataForPersistence` method, which should return an array of key/value
 * pairs.
 *
 * @internal
 * @subpackage WabiORM.Model
 * @param object $model
 * @return array
 */
function model_data(object $model): array {
    $data = \get_object_vars($model);

    if (\method_exists($model, 'withDataForPersistence')) {
        $data = $model->withDataForPersistence();
    }

    invariant(
        \is_array($data),
        'model_data() was unable to determine the data for the given model'
    );

    return $data;
}

/**
 * Returns a q() compatible data structure for a insert into the database.
 *
 * @internal
 * @subpackage WabiORM.Model
 * @param object $model
 * @return array
 */
function model_data_for_insert(object $model): array {
    $info = model_info($model);
    $data = model_data($model);

    unset($data[$info['primaryKey']]);

    return [
        'table' => $info['tableName'],
        'fields' => \array_keys($data),
        'values' => \array_values($data),
    ];
}

/**
 * Returns a q() compatible data structure for a updating a model in the
 * database.
 *
 * @internal
 * @subpackage WabiORM.Model
 * @param object $model
 * @return array
 */
function model_data_for_update(object $model): array {
    $info = model_info($model);
    $data = model_data($model);

    $primaryKey = $info['primaryKey'];
    $id = $data[$primaryKey];

    unset($data[$primaryKey]);

    return [
        'fields' => $data,
        'id' => $id,
        'key' => $primaryKey,
        'table' => $info['tableName'],
    ];
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
