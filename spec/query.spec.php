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
});
