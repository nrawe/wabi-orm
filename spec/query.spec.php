<?php

use function WabiORM\q;

describe('q()', function () {

    it('replaces raw expressions with their values', function () {
        [$query, $params] = q('SELECT * FROM {!! table !!}', ['table' => 'a']);

        expect($query)->toEqual('SELECT * FROM a');
        expect($params)->toBeEmpty();
    });

    it('throws if a raw expression cannot be reached', function () {
        $tryer = function () { q('SELECT * FROM {!! table !!}', []); };

        expect($tryer)->toThrow();
    });

    it('replaces equality bindings', function () {
        [$query, $params] = q('SELECT * FROM a WHERE {{=b}}', ['b' => 1]);

        expect($query)->toEqual('SELECT * FROM a WHERE b = ?');
        expect($params)->toEqual([1]);

        [$query, $params] = q('SELECT * FROM a WHERE {{=b}}', ['b' => [1, 2]]);

        expect($query)->toEqual('SELECT * FROM a WHERE b in (?, ?)');
        expect($params)->toEqual([1, 2]);
    });

    it('replaces inequality bindings', function () {
        [$query, $params] = q('SELECT * FROM a WHERE {{!b}}', ['b' => 1]);

        expect($query)->toEqual('SELECT * FROM a WHERE b != ?');
        expect($params)->toEqual([1]);
    });
});
