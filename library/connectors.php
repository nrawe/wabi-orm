<?php
declare(strict_types=1);

/**
 * This subpackage contains some base connection factories for convenience.
 */
namespace WabiORM;

use PDO;

/**
 * A basic MySQL connection factory.
 *
 * @subpackage WabiORM.Connectors
 * @param string $host
 * @param string $db
 * @param string $user
 * @param string $pwd
 * @return \PDO
 */
function mysql(string $host, string $db, string $user, string $pwd): PDO {
    $dsn = 'mysql:host='.$host.';dbname='.$db.';';
    $options = [
        PDO::ATTR_CASE => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];

    return new PDO($dsn, $user, $pwd, $options);
}