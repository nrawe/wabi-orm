<?php
declare(strict_types=1);

/**
 * This subpackage provides ActiveRecord functions.
 */
namespace WabiORM;

/**
 * Returns a model instance for the owner of the given model.
 *
 * @subpackage WabiORM.ORM
 * @param string $related
 * @param object $model
 * @param callable $connection
 * @return object|null
 */
function belongs_to(string $related, object $model, callable $connection = null) {
    $connection = reader($connection);

    $info = model_info($related);

    $query = q('select * from {*table} where {*key} = {id}', [
        'id' => $model->{$info->relationKey()},
        'key' => $info->primaryKey(),
        'table' => $info->tableName(),
    ]);

    return first(hydrate($related, $connection(...$query)));
}

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
 * Attempts to remove the model from the database.
 *
 * @subpackage WabiORM.ORM
 * @param object $model
 * @param callable $connection (optional)
 * @return boolean
 */
function delete(object $model, callable $connection = null): bool {
    $connection = writer($connection);

    $query = q(
        'delete from {*table} where {*key} = {id}',
        model_data_for_delete($model)
    );

    return was_execution_successful($connection(...$query));
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
        'key' => $info->primaryKey(),
        'table' => $info->tableName(),
    ]);

    return first(hydrate($model, $connection(...$query)));
}

/**
 * Returns the first record from the table for the model.
 *
 * @subpackage WabiORM.ORM
 * @param string $model
 * @param callable $connection
 * @return object|null
 */
function find_first(string $model, callable $connection = null) {
    $connection = reader($connection);

    $info = model_info($model);

    $query = q('select * from {*table} limit 0, 1', [
        'table' => $info->tableName(),
    ]);

    return first(hydrate($model, $connection(...$query)));
}

/**
 * Returns the last record from the table for the model.
 *
 * @subpackage WabiORM.ORM
 * @param string $model
 * @param callable $connection
 * @return object|null
 */
function find_last(string $model, callable $connection = null) {
    $connection = reader($connection);

    $info = model_info($model);

    $query = q('select * from {*table} limit 0, 1 order by {*key} desc', [
        'key' => $info->primaryKey(),
        'table' => $info->tableName(),
    ]);

    return first(hydrate($model, $connection(...$query)));
}

/**
 * Returns a model instance for the owner of the given model.
 *
 * @subpackage WabiORM.ORM
 * @param string $related
 * @param object $model
 * @param callable $connection
 * @return object[]
 */
function has_many(string $related, object $model, callable $connection = null) {
    $connection = reader($connection);

    $modelInfo = model_info($model);
    $relatedInfo = model_info($related);

    $query = q('select * from {*table} where {*key} = {id}', [
        'id' => $model->{$modelInfo->primaryKey()},
        'key' => $modelInfo->relationKey(),
        'table' => $relatedInfo->tableName(),
    ]);

    return hydrate($related, $connection(...$query));
}

/**
 * Returns a model instance for the owner of the given model.
 *
 * @subpackage WabiORM.ORM
 * @param string $related
 * @param object $model
 * @param callable $connection
 * @return object|null
 */
function has_one(string $related, object $model, callable $connection = null) {
    return first(has_many($related, $model, $connection));
}

/**
 * Provides a mechanism for generically executing a query and hydrating the
 * result.
 * 
 * @subpackage WabiORM.ORM
 * @param string $model
 * @param string $query The query template string for use with q()
 * @param array $params The query parameters for use with q()
 * @param callable $connection
 * @return object[]
 */
function read(string $model, string $query, array $params = [], callable $connection = null): array {
    $connection = reader($connection);

    $info = model_info($model);

    $params['table'] = $info->tableName();

    return hydrate($model, $connection(...q($query, $params)));
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
