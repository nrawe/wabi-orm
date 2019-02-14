<?php

require_once __DIR__ . '/model.fixtures.php';

use function WabiORM\{create_model, model_info};

describe('create_model()', function () {

    it('fails when trying to create a non-existent object', function () {
        $tryer = function () { create_model('ClassThatDoesNotExist'); };

        expect($tryer)->toThrow();
    });

    it('fails when trying to create a model with required args', function () {
        $tryer = function () {
            create_model(ModelWithRequiredArguments::class); 
        };

        expect($tryer)->toThrow();
    });

    it('allows bypassing of model constructors', function () {
        $model = create_model(ModelWithRequiredArguments::class, false);

        expect($model)->toBeAnInstanceOf(ModelWithRequiredArguments::class);
    });

    it('returns a model with optional arguments', function () {
        $model = create_model(ModelWithoutRequiredArguments::class);

        expect($model)->toBeAnInstanceOf(ModelWithoutRequiredArguments::class);
    });
});

describe('model_info()', function () {
    it('fails when an invalid argument is given', function () {
        $tryer = function () { model_info(1); };

        expect($tryer)->toThrow();
    });

    it('returns the default primary key from an object', function () {
        $info = model_info(new ModelWithoutOverrides);

        expect($info)->toEqual([
            'primaryKey' => 'id',
            'tableName' => 'model_without_overridess',
            'relationKey' => 'model_without_overrides_id'
        ]);
    });

    it('returns the primary key stated on the object', function () {
        $info = model_info(new ModelWithOverrides);

        expect($info)->toEqual([
            'primaryKey' => 'overridden',
            'tableName' => 'custom_table',
            'relationKey' => 'custom_id',
        ]);
    });

    it('returns the primary key from a class reference', function () {
        $info = model_info(ModelWithOverrides::class);

        expect($info)->toEqual([
            'primaryKey' => 'overridden',
            'tableName' => 'custom_table',
            'relationKey' => 'custom_id',
        ]);
    });
});
