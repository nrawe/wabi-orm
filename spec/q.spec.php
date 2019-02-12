<?php

use function WabiORM\q;

describe('q()', function () {

    it('processes binding flags', function () {
        $cases = [
            // Direct processor
            ['where {a}', ['a' => 1], 'where ?', [1]],

            // Equality processor with a single value
            ['where {=a}', ['a' => 1], 'where a = ?', [1]],
            ['where {=a}', ['a' => [1]], 'where a = ?', [1]],
            ['where {!a}', ['a' => 1], 'where a != ?', [1]],
            ['where {!a}', ['a' => [1]], 'where a != ?', [1]],

            // Equality processor with multiple values
            ['where {=a}', ['a' => [1, 2]], 'where a in (?, ?)', [1, 2]],
            ['where {!a}', ['a' => [1, 2]], 'where a not in (?, ?)', [1, 2]],

            // Like processor with a single value
            ['where {%a}', ['a' => 'b'], 'where a like ?', ['%b']],
            ['where {%a%}', ['a' => 'b'], 'where a like ?', ['%b%']],
            ['where {a%}', ['a' => 'b'], 'where a like ?', ['b%']],
            
            // Like processor with multiple values
            ['where {%a}', ['a' => ['b', 'c']], 'where (a like ? or a like ?)', ['%b', '%c']],
            ['where {%a%}', ['a' => ['b', 'c']], 'where (a like ? or a like ?)', ['%b%', '%c%']],
            ['where {a%}', ['a' => ['b', 'c']], 'where (a like ? or a like ?)', ['b%', 'c%']],

            // Greater processor with a single value
            ['where {>a}', ['a' => 1], 'where a > ?', [1]],
            ['where {>=a}', ['a' => 1], 'where a >= ?', [1]],

            // Greater processor with single values
            ['where {>a}', ['a' => [1, 2]], 'where (a > ? or a > ?)', [1, 2]],
            ['where {>=a}', ['a' => [1, 2]], 'where (a >= ? or a >= ?)', [1, 2]],

            // Lesser processor with a single value
            ['where {<a}', ['a' => 1], 'where a < ?', [1]],
            ['where {<=a}', ['a' => 1], 'where a <= ?', [1]],

            // Lesser processor with single values
            ['where {<a}', ['a' => [1, 2]], 'where (a < ? or a < ?)', [1, 2]],
            ['where {<=a}', ['a' => [1, 2]], 'where (a <= ? or a <= ?)', [1, 2]],

            // Raw value
            ['from {*a}', ['a' => 'table'], 'from table', []],
        ];

        foreach ($cases as [$template, $data, $query, $params]) {
            $result = q($template, $data);

            expect($result)->toEqual([$query, $params]);
        }
    });
});





