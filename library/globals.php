<?php
declare(strict_types=1);

/**
 * This subpackage provides helpers for sharing connections.
 */
namespace WabiORM;

use RuntimeException;

/**
 * Stores the global read connection.
 * 
 * When called with an argument, this will either set or unset the connection.
 * 
 * When called without an argument, this will return the current connection, if
 * available.
 *
 * @subpackage WabiORM.Globals
 * @param callable|null $connection The connection created by connect
 * @return callable|void
 */
function global_read() {
    static $reader;

    if (\func_num_args() > 0) {
        $reader = \func_get_arg(0);
        return;
    }

    invariant(
        is_callable($reader), 
        'global_read(): no connection has been set globally.'
    );

    return $reader;
}

/**
 * Stores the global write connection.
 * 
 * When called with an argument, this will either set or unset the connection.
 * 
 * When called without an argument, this will return the current connection, if
 * available.
 *
 * @subpackage WabiORM.Globals
 * @param callable|null $connection The connection created by connect
 * @return callable|void
 */
function global_write() {
    static $reader;

    if (\func_num_args() > 0) {
        $reader = \func_get_arg(0);
        return;
    }

    invariant(
        is_callable($reader), 
        'global_write(): no connection has been set globally.'
    );

    return $reader;
}
