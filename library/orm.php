<?php
declare(strict_types=1);

/**
 * This subpackage provides ActiveRecord functions.
 */
namespace WabiORM;

/**
 * Attempts to create the model in the database.
 *
 * @subpackage WabiORM.ORM
 * @param object $model
 * @param callable $connection
 * @return boolean
 */
function create(object $model, callable $connection = null): bool {
    $connection = writer($connection);

    $info = model_info($model);
    $properties = \get_object_vars($model);

    $query = q('insert into {*table} ({*fields}) values ({values})', [
        'table' => $info['tableName'],
        'fields' => \array_keys($properties),
        'values' => \array_values($properties),
    ]);

    return was_execution_successful($connection(...$query));
}

/**
 * Attempts to return a model of the given type from the database.
 *
 * @subpackage WabiORM.ORM
 * @param string $model
 * @param scalar $id
 * @param callable $connection
 * @return object|null
 */
function find_one(string $model, $id, callable $connection = null) {
    $connection = reader($connection);

    $info = model_info($model);

    $query = q('select * from {*table} where {*key} = {id}', [
        'id' => $id,
        'key' => $info['primaryKey'],
        'table' => $info['tableName'],
    ]);

    return first(hydrate($model, $connection(...$query)));
}

/**
 * Yeilds a connection that can be used for reading.
 *
 * @internal 
 * @subpackage WabiORM.ORM
 * @param callable $connection (optional)
 * @return callable
 */
function reader(callable $connection = null): callable {
    return $connection ?? global_read();
}

/**
 * Yeilds a connection that can be used for writing.
 *
 * @internal 
 * @subpackage WabiORM.ORM
 * @param callable $connection (optional)
 * @return callable
 */
function writer(callable $connection = null): callable {
    return $connection ?? global_write();
}
