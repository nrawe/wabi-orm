<?php

require_once __DIR__ . '/../vendor/autoload.php';

use function WabiORM\{
    array_accessor,
    array_accessor_cached, 
    average_execution_time, 
    readable_elapsed_time, 
    run_for
};

$data = [
    'a' => 1,
    'b' => [1, 2, 3, 4],
    'c' => 'this is a thing',
    'd' => true,
];

$keys = array_keys($data);
$keyCount = count($data) - 1;

$old = array_accessor($data);
$new = array_accessor_cached($data);

$randomKey = function () use ($keys, $keyCount) {
    return $keys[random_int(0, $keyCount)];
};

foreach ([10, 100, 1000] as $repetitions) {
    foreach ($keys as $key) {
        $oldTime = average_execution_time($repetitions, function () use ($old, $key) {
            $old($key);
        });
        
        $newTime = average_execution_time($repetitions, function () use ($new, $key) {
            $new($key);
        });
        
        if ($newTime > $oldTime) {
            $times = round($newTime / $oldTime, 2);
        
            echo 'Old time was ', $times, ' faster at ', $repetitions, ' repetitions for key ', $key, PHP_EOL;
        } else {
            $times = round($oldTime / $newTime, 2);
        
            echo 'New time was ', $times, ' faster at ', $repetitions, ' repetitions for key ', $key, PHP_EOL;
        }
    }
}

echo 'Old executions made: ', run_for(1, function () use ($old, $data, $randomKey) {
    $old($randomKey());
}), PHP_EOL;

echo 'New executions made: ', run_for(1, function () use ($new, $data, $randomKey) {
    $new($randomKey());
}), PHP_EOL;


