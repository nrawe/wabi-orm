<?php
declare(strict_types=1);

/**
 * This subpackage provides ActiveRecord functions.
 */
namespace WabiORM;

/**
 * Represents a relationship between models.
 */
interface RelationInterface {
    /**
     * The name of the class to be hydrated when the relationship is loaded.
     *
     * @return string
     */
    public function className(): string;

    /**
     * Returns whether a single model should be returned.
     *
     * @return boolean
     */
    public function isOne(): bool;

    /**
     * Returns whether an array of model should be returned.
     *
     * @return boolean
     */
    public function isMany(): bool;

    /**
     * Returns the name of the key in the related models' table which can be
     * tested for the related model(s).
     *
     * @return string
     */
    public function foreignKeyName(): string;

    /**
     * Returns the name of the key in the local models' table which can be
     * tested for the related model(s).
     *
     * @return string
     */
    public function localKeyName(): string;

    /**
     * Returns the name of the table which should be looked at for the 
     * related model(s).
     *
     * @return string
     */
    public function tableName(): string;
}

/**
 * {@inheritDoc}
 */
final class Relationship implements RelationInterface {

    /**
     * The class name.
     *
     * @var string
     */
    protected $class;

    /**
     * The foreign key name.
     *
     * @var string
     */
    protected $foreignKey;

    /**
     * The local key name.
     *
     * @var string
     */
    protected $localKey;

    /**
     * Whether the result set should be an array.
     *
     * @var bool
     */
    protected $many;

    /**
     * The table name.
     *
     * @var string
     */
    protected $table;

    /**
     * Creates a new instance of the Relationship.
     *
     * @param string $class
     * @param string $localKey
     * @param string $foreignKey
     * @param string $table
     * @param bool $many
     */
    public function __construct(string $class, string $localKey, string $foreignKey, string $table, bool $many = true) {
        $this->class = $class;
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
        $this->many = $many;
        $this->table = $table;
    }
    
    /**
     * {@inheritDoc}
     */
    public function className(): string {
        return $this->class;
    }

    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function isOne(): bool {
        return $this->many === false;
    }
    
    /**
     * Undocumented function
     *
     * @return boolean
     */
    public function isMany(): bool {
        return $this->many === true;
    }

    /**
     * {@inheritDoc}
     */
    public function foreignKeyName(): string {
        return $this->foreignKey;
    }

    /**
     * {@inheritDoc}
     */
    public function localKeyName(): string {
        return $this->localKey;
    }

    /**
     * {@inheritDoc}
     */
    public function tableName(): string {
        return $this->table;
    }
}

/**
 * Returns a model instance for the owner of the given model.
 *
 * @subpackage WabiORM.ORM
 * @param string $related The reference to the related class 
 * @param object $model The reference of the class to load the relation for
 * @return object|null
 */
function belongs_to(string $related, string $model): RelationInterface {
    $info = model_info_cached($related);

    return new Relationship(
        $related,
        $info->relationKey(),
        $info->primaryKey(),
        $info->tableName(), 
        false
    );
}

/**
 * Attempts to create the model in the database.
 *
 * @subpackage WabiORM.ORM
 * @param object $model The model instance to load
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
 * @param object $model The model instance to delete
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
 * @param string $model The class reference of the model to load
 * @param scalar $id The ID of the model
 * @param callable $connection (optional)
 * @return object|null
 */
function find_one(string $model, $id, callable $connection = null) {
    $connection = reader($connection);

    $info = model_info_cached($model);

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
 * @param string $model The class reference of the model to load
 * @param callable $connection
 * @return object|null
 */
function find_first(string $model, callable $connection = null) {
    $connection = reader($connection);

    $info = model_info_cached($model);

    $query = q('select * from {*table} limit 0, 1', [
        'table' => $info->tableName(),
    ]);

    return first(hydrate($model, $connection(...$query)));
}

/**
 * Returns the last record from the table for the model.
 *
 * @subpackage WabiORM.ORM
 * @param string $model The class reference of the model to load
 * @param callable $connection
 * @return object|null
 */
function find_last(string $model, callable $connection = null) {
    $connection = reader($connection);

    $info = model_info_cached($model);

    $query = q('select * from {*table} limit 0, 1 order by {*key} desc', [
        'key' => $info->primaryKey(),
        'table' => $info->tableName(),
    ]);

    return first(hydrate($model, $connection(...$query)));
}

/**
 * Returns the records with the given relationship to those models passed.
 *
 * The records will be hydrated to the model of the relationship.
 * 
 * @subpackage WabiORM.ORM
 * @param RelationInterface $relation
 * @param array|object|scalar $models
 * @param callable $connection
 * @return object[]
 */
function find_related(RelationInterface $relation, $models, callable $connection = null) {
    $connection = reader($connection);

    $ids = map(to_array($models), function ($model) use ($relation) {
        // Allow users to be able to specify raw ids rather than through models.
        if (\is_scalar($model)) {
            return $model;
        }
        
        return $model->{$relation->localKeyName()};
    });
    
    $key = $relation->foreignKeyName();
    $query = q("select * from {*table} where {=$key}", [
        $key => $ids,
        'table' => $relation->tableName(),
    ]);

    $data = hydrate($relation->className(), $connection(...$query));

    return $relation->isOne() ? first($data) : $data;
}

/**
 * Returns a model instance for the owner of the given model.
 *
 * @subpackage WabiORM.ORM
 * @param string $related The reference to the related class
 * @param object $model The reference to the class to load the relation for
 * @return object[]
 */
function has_many(string $related, string $model): RelationInterface {
    $modelInfo = model_info_cached($model);
    $relatedInfo = model_info_cached($related);

    return new Relationship(
        $related,
        $modelInfo->primaryKey(),
        $modelInfo->relationKey(), 
        $relatedInfo->tableName(),
    );
}

/**
 * Returns a model instance for the owner of the given model.
 *
 * @subpackage WabiORM.ORM
 * @param string $related The reference to the related class
 * @param object $model The reference to the class to load the relation for
 * @return object|null
 */
function has_one(string $related, string $model): RelationInterface {
    $modelInfo = model_info_cached($model);
    $relatedInfo = model_info_cached($related);

    return new Relationship(
        $related,
        $modelInfo->primaryKey(),
        $modelInfo->relationKey(), 
        $relatedInfo->tableName(), 
        false
    );
}

/**
 * Provides a mechanism for generically executing a query and hydrating the
 * result.
 * 
 * @subpackage WabiORM.ORM
 * @param string $model The class reference of the model to load
 * @param string $query The query template string for use with q()
 * @param array $params The query parameters for use with q()
 * @param callable $connection
 * @return object[]
 */
function read(string $model, string $query, array $params = [], callable $connection = null): array {
    $connection = reader($connection);

    $info = model_info_cached($model);

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
 * @param object $model The instance of the model to update
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
