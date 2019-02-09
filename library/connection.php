<?php
declare(strict_types=1);

namespace WabiORM;

use PDO;
use PDOStatement;
use RuntimeException;

/**
 * Decorates a PDO connection to facilitate middleware during query execution.
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
 * @param PDO $connection
 * @param string $query
 * @param array $params
 * @return PDOStatement
 * @internal
 */
function execute_query() {
    return function(PDO $connection, string $query, array $params): PDOStatement {
        $statement = $connection->prepare($query);
        $statement->execute($params);

        return $statement;
    };
}
