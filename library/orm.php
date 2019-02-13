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
 * @param callable $connection (optional)
 * @return boolean
 */
function create(object $model, callable $connection = null) {
    $connection = writer($connection);

    $query = q(
        'insert into {*table} ({*fields}) values ({values})',
        model_data_for_insert($model)
    );

    $result = $connection(...$query);

    invariant(
        was_execution_successful($result),
        'inserting record failed'
    );

    return last_insert_id($result);
}

/**
 * Attempts to return a model of the given type from the database.
 *
 * @subpackage WabiORM.ORM
 * @param string $model
 * @param scalar $id
 * @param callable $connection (optional)
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
 * Attempts to save a model in the database
 *
 * @subpackage WabiORM.ORM
 * @param object $model
 * @param callable $connection (optional)
 * @return void
 */
function save(object $model, callable $connection = null) {
    return is_persisted($model)
        ? update($model, $connection)
        : create($model, $connection);
}

/**
 * Attempts to update the given model in the database.
 *
 * @subpackage WabiORM.ORM
 * @param object $model
 * @param callable $connection (optional)
 * @return boolean
 */
function update(object $model, callable $connection = null): bool {
    $connection = writer($connection);

    $query = q(
        'update {*table} set {...fields} where {*key} = {id}',
        model_data_for_update($model)
    );

    return was_execution_successful($connection(...$query));
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
