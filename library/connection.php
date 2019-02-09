<?php
declare(strict_types=1);

namespace WabiORM;

use PDO;
use PDOStatement;

/**
 * Decorates a PDO connection to facilitate middleware during query execution.
 */
function connect(PDO $connection): callable {
    return function (string $query, array $params = []) use ($connection): PDOStatement {
        $statement = $connection->prepare($query);
        $statement->execute($params);

        return $statement;
    };
}


