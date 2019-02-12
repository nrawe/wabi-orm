<?php
declare(strict_types=1);

/**
 * This subpackage provides an API for executing queries against a database
 * via middleware, allowing the execution process to be augmented easily.
 * 
 * For example, logging of query execution time, or modifying the query before
 * execution, can be broken down into small middlewares.
 */
namespace WabiORM;

use PDO;
use PDOStatement;
use RuntimeException;

/**
 * Decorates a PDO connection to facilitate middleware during query execution.
 * 
 * @subpackage WabiORM.Connect
 * @param PDO $connection
 * @param callable[] $middlewars
 * @return callable
 */
function connect(PDO $connection, array $middlewares = []): callable {
    // Ensure that query execution is always handled last.
    array_push($middlewares, execute_query());

    // Compose the middlewares into an executable chain.
    $fn = compose_middleware(...$middlewares);

    // Return the executor interface.
    return function (string $query, array $params = []) use ($connection, $fn) {
        return $fn($connection, $query, $params);
    };
}

/**
 * Returns a new function that acts as a middleware pipeline.
 * 
 * @internal
 * @subpackage WabiORM.Connect
 * @param callable[] $middlewares
 * @return callable
 */
function compose_middleware(callable ...$middlewares): callable {
    $unreachableMiddleware = function () {
        throw new RuntimeException(
            'compose_middleware(): the middleware stack was executed without ' .
            'the possibility of a return value, which indicates that the ' . 
            'query was not executed successfully.',
        );
    };

    return array_reduce(
        array_reverse($middlewares),
        function ($nextMiddleware, $currentMiddleware) {
            return function (...$args) use ($nextMiddleware, $currentMiddleware) {
                array_push($args, $nextMiddleware);

                return $currentMiddleware(...$args);
            };
        },
        $unreachableMiddleware,
    );
}

/**
 * Middleware for connect which performs the execution of a query.
 * 
 * @internal
 * @subpackage WabiORM.Connect
 * @return callable
 */
function execute_query() {
    return function(PDO $conn, string $query, array $params): PDOStatement {
        $statement = $conn->prepare($query);
        $statement->execute($params);

        return $statement;
    };
}
