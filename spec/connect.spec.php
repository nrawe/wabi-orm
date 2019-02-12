<?php

use function Eloquent\Phony\Kahlan\on;
use function WabiORM\connect;

describe('connection', function () {
    beforeEach(function (PDO $db, PDOStatement $stmt) {
        $this->db = $db;
        $this->stmt = $stmt; 
    });

    describe('connect()', function () {
        it('provides a minimal interface over PDO', function () {
            on($this->db)->prepare->with('query')->returns($this->stmt);

            $execute = connect($this->db);

            expect($execute('query'))->toBeAnInstanceOf('PDOStatement');
        });

        it('executes custom middleware during query execution', function () {
            $middleware = function ($c, $q, $p, $next) {
                // We'll change the query before execution and use that to track
                // whether the middleware was executed.
                return $next($c, 'b', $p);
            };

            on($this->db)->prepare->with('b')->returns($this->stmt);

            $execute = connect($this->db, [$middleware]);

            expect($execute('query'))->toBeAnInstanceOf('PDOStatement');
        });
    });
});
