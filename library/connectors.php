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
 * @see https://secure.php.net/manual/en/ref.pdo-mysql.connection.php
 * @param string $host The hostname of the database server
 * @param string $db The schema name
 * @param string $user The username for authentication
 * @param string $pwd The password for authentication
 * @return \PDO
 */
function mysql(string $host, string $db, string $user, string $pwd): PDO {
    $dsn = 'mysql:host='.$host.';dbname='.$db.';';

    return new PDO($dsn, $user, $pwd, pdo_options());
}

/**
 * A basic SQLite connection factory.
 *
 * @subpackage WabiORM.Connectors
 * @see https://secure.php.net/manual/en/ref.pdo-sqlite.connection.php
 * @param string $file The path to the file containing the database
 * @return \PDO
 */
function sqlite(string $file = ':memory:'): PDO {
    return new PDO('sqlite:' . $file, '', '', pdo_options());
}

/**
 * Returns the options for PDO to work with the connectors.
 *
 * @subpackage WabiORM.Connectors
 * @return array
 */
function pdo_options(): array {
    return [
        PDO::ATTR_CASE => PDO::CASE_LOWER,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ];
}
