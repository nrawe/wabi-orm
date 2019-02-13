# Using connections in WabiORM

WabiORM ships with a wrapper over PDO to abstract query execution called
`connect()`. The primary goals of this is to focus usage and enable simple
testing.

The secondary goal of this abstraction is to provide a middleware system,
comparable to those now common in Web Apps. This allows for augmentation of
the query execution process without requiring an class/event system.

## Connecting to a database

The below highlights the various options for connecting to a database:

```php
use function WabiORM\{connect, echo_query, middleware, mysql};

// By default, you don't have to do anything special...
$execute = connect($pdo);

// ... But you can wrap query execution with middleware if you want...
$execute = connect($pdo, [echo_query()]);

// ... And you can use a factory for the connection, too.
$execute = connect(mysql('localhost', 'root', 'username', 'password'));

// Use the returned function to execute queries with bound parameters.
$result = $execute($query, $params);

// The result will be an instance of WabiORM\ConnectResultInterface, which
// allows access to both the connection and statement.
echo $result->statement()->errorCode();

// You can set this executor function globally, for convenience.
use function WabiORM\{global_read, global_write};

global_read($execute);

assert($execute === global_read());
```

## Writing a middleware

Middleware allows the query execution process to be augemented. For example, if
we wanted to log query execution, we might do the following:

```php
// This function will be invoked prior to query execution
$echo = function (PDO $conn, string $query, array $params, callable $next) {
    var_dump($query, $params);
    
    // Call the next middleware in the chain
    return $next($conn, $query, $params);
};

// Middleware is given as the second argument of connect
$executor = connect($conn, [$echo]);
```

The only rule is that a `WabiORM\ConnectResultInterface` is ultimately expected
to be returned.

## Testing

The advantage of `connect()` is that it can enable a tight interface for
testing. For example:

```php
use function WabiORM\{find_one, global_read};

global_read(function ($query, $params) {
    expect($query)->toEqual('....');

    // Any mocking library can be used here (mock is not provided)
    return mock(PDOStatement::class)->fetchAll->returns(['pass']);
});

expect(find_one(Model::class, 1))->toEqual('pass');
```

This is somewhat contrived, but demonstrates what's possible.
