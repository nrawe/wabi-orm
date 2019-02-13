<?php

use WabiORM\ConnectResult;
use function Eloquent\Phony\Kahlan\on;
use function WabiORM\{find_one, global_read};

class MyModel {

}

describe('orm', function () {
    beforeEach(function (PDO $db, PDOStatement $stmt) {
        $this->db = $db;
        $this->stmt = $stmt;
    });

    describe('find_one', function () {

        it('returns from the global_read()', function () {
            $db = $this->db;
            $stmt = $this->stmt;
            
            global_read(function ($query, $params) use ($db, $stmt) {
                expect($query)->toEqual('select * from my_models where id = ?');
                expect($params)->toEqual([1]);

                return new ConnectResult($db, $stmt);
            });

            on($stmt)->fetchAll->with(PDO::FETCH_CLASS, MyModel::class)->returns(['pass']);

            expect(find_one(MyModel::class, 1))->toEqual('pass');
        });
    });
});
