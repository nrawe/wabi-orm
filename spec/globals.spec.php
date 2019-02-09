<?php

use function WabiORM\{global_read, global_write};

describe('globals', function () {

    describe('global_read()', function () {

        it('sets a global read connection', function () {
            $reader = function () { };
            
            global_read($reader);

            expect(global_read())->toEqual($reader);
        });

        it('unsets a global read connection', function () {
            $reader = function () { };
            
            global_read($reader);
            global_read(null);

            $attempt = function () { return global_read(); };

            expect($attempt)->toThrow();
        });
    });

    describe('global_write()', function () {

        it('sets a global write connection', function () {
            $writer = function () { };
            
            global_write($writer);

            expect(global_write())->toEqual($writer);
        });

        it('unsets a global write connection', function () {
            $writer = function () { };
            
            global_write($writer);
            global_write(null);

            $attempt = function () { return global_write(); };

            expect($attempt)->toThrow();
        });
    });
});
