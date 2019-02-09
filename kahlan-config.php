<?php

// disable monkey-patching for Phony classes
$this->commandLine()->set('exclude', ['Eloquent\Phony']);

// install the plugin once autoloading is available
Kahlan\Filter\Filters::apply($this, 'run', function (callable $chain) {
    Eloquent\Phony\Kahlan\install();

    return $chain();
});
