<?php

require_once __DIR__ . '/vendor/autoload.php';

use function WabiORM\{
    belongs_to,
    connect,
    create,
    delete,
    find_one,
    global_read,
    global_write,
    has_many,
    has_one,
    mysql,
    save,
    update,
};

$echo = function (PDO $conn, $query, $params, $next) {
    var_dump($query, $params);

    return $next($conn, $query, $params);
};

$connection = connect(mysql('localhost', 'bronze_cobra', 'root', ''), [$echo]);

global_read($connection);
global_write($connection);

class Branch {

    public $id;

    public $address_id;

    public function address(): Address {
        return belongs_to(Address::class, $this);
    }

    public function withTableName(): string {
        return 'branches';
    }
}

class Address {
    public function branches() {
        return has_many(Branch::class, $this);
    }

    public function withTableName(): string {
        return 'addresses';
    }
}

// $address = find_one(Address::class, 30);
// $address->id = null;

// $id = save($address);

// $address = find_one(Address::class, $id);
// $address->building_name = 'This is my building';

// save($address);

// delete($address);

$agent = find_one(Branch::class, 1);
$address = $agent->address();

var_dump($agent, $address);


foreach ($address->branches() as $branch) {
    echo $branch->name, PHP_EOL;
}
