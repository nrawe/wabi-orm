<?php

class ModelWithRequiredArguments {
    public function __construct($required) {

    }
}

class ModelWithoutRequiredArguments {
    public function __construct($optional = true) {

    }
}

class ModelWithoutOverrides {

}

class ModelWithOverrides {
    public function withPrimaryKey(): string {
        return 'overridden';
    }

    public function withTableName(): string {
        return 'custom_table';
    }

    public function withRelationKey(): string {
        return 'custom_id';
    }
}
