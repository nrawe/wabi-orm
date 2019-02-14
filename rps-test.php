<?php

require_once __DIR__ . '/vendor/autoload.php';

use function WabiORM\{
    belongs_to,
    connect,
    create,
    delete,
    find_first,
    find_one,
    global_read,
    global_write,
    has_many,
    has_one,
    mysql,
    read,
    save,
    update,
};

$connection = connect(mysql('localhost', 'rps_demo', 'root', ''));

global_read($connection);

trait RPSEntity {
    public $synchdel;

    public $synchrec;

    public function isDeleted(): bool {
        return (bool)$this->synchdel;
    }

    public function withTableName(): string {
        static $lookup = [
            Contact::class => 'cnt',
            Negotiator::class => 'negs',
            Office::class => 'office',
            Property::class => 'prp',
        ];

        return $lookup[get_class($this)];
    }

    public function withRelationKey(): string {
        static $lookup = [
            Contact::class => 'cntcode',
            Negotiator::class => 'negcode',
            Office::class => 'offcode',
            Property::class => 'prpcode',
        ];

        return $lookup[get_class($this)];
    }

    public function withPrimaryKey(): string {
        return $this instanceof Contact ? 'cntcode' : 'code';
    }
}

class Contact {
    use RPSEntity;

    public function properties() {
        return has_many(Property::class, $this);
    }
}

class Property {
    use RPSEntity;

    public function contact() {
        return belongs_to(Contact::class, $this);
    }

    public function negotiator() {
        return belongs_to(Negotiator::class, $this);
    }

    public function office() {
        return belongs_to(Office::class, $this);
    }
}

class Negotiator {
    use RPSEntity;

    public function properties() {
        return has_many(Property::class, $this);
    }

    public function office() {
        return belongs_to(Office::class, $this);
    }
}

class Office {
    use RPSEntity;

    public function negotiators() {
        return has_many(Negotiator::class, $this);
    }

    public function properties() {
        return has_many(Property::class, $this);
    }
}

$property = find_first(Property::class);

var_dump(
    $property->contact(),
    $property->office(),
    $property->negotiator(),
);

function find_properties_by_status(string ...$statuses) {
    return read(
        Property::class,
        'select * from {*table} where synchdel = 0 and {=salestatus}',
        ['salestatus' => $statuses],
    );
}

echo count(find_properties_by_status('WD')), ' properties', PHP_EOL;
