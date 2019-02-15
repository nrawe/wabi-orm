<?php

require_once __DIR__ . '/vendor/autoload.php';

use function WabiORM\{
	average_execution_time, model_info, model_info_cached, readable_elapsed_time, run_for
};

class ModelWithoutOverrides {
	
}

class ModelWithOverrides {
    public function withPrimaryKey() {
        return 'id';
    }

    public function withRelationKey() {
        return 'key_id';
    }

    public function withTableName() {
        return 'table';
    }
}

class ModelC {

}

class ModelD {
    public function withPrimaryKey() {
        return 'id';
    }
}

$randomModel = function () {
    $models = [
        ModelWithOverrides::class, 
        ModelWithoutOverrides::class, 
        ModelC::class, 
        ModelD::class
    ];

    return $models[random_int(0, count($models) - 1)];
};

foreach ([10, 100, 1000] as $repetitions) {
    $model = $randomModel();

    $oldTime = average_execution_time($repetitions, function () use ($model) {
        model_info($model);
    });
    
    $newTime = average_execution_time($repetitions, function () use ($model) {
        model_info_cached($model);
    });
    
    if ($newTime > $oldTime) {
        $times = round($newTime / $oldTime, 2);
    
        echo 'Old time was ', $times, ' faster at ', $repetitions, ' repetitions', PHP_EOL;
    } else {
        $times = round($oldTime / $newTime, 2);
    
        echo 'New time was ', $times, ' faster at ', $repetitions, ' repetitions', PHP_EOL;
    }
}

echo 'Old executions made: ', run_for(1, function () use ($randomModel) {
    model_info($randomModel());
}), PHP_EOL;

echo 'New executions made: ', run_for(1, function () use ($randomModel) {
    model_info_cached($randomModel());
}), PHP_EOL;
