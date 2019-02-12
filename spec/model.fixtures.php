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
}
