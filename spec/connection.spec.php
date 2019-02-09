<?php

use PDO;
use PDOStatement;
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
    });
});
