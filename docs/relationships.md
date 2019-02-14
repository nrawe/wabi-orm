# Working with Relationships

WabiORM supports loading relationships between objects in a similar way to
other object-relational mappers:

```php

use function WabiORM\{belongs_to, has_many, has_one};

class Address {
    public $id;

    public $person_id;

    public function letters() {
        return has_many(Letter::class, $this);
    }

    public function person() {
        return belongs_to(Person::class, $this);
    }
}

class Person {
    public $id;

    public function address() {
        return has_one(Address::class, $this);
    }

    public function letters() {
        return has_many(Letter::class, $this);
    }
}

class Letter {
    public $id;

    public $address_id;

    public $person_id;

    public $subject;

    public function address() {
        return belongs_to(Address::class, $this);
    }

    public function person() {
        return belongs_to(Person::class, $this);
    }
}

$person = find_one(Person::class, 1);
$address = $person->address();

$address->person()->id === $person->id;

foreach ($address->letters() as $letter) {
    echo $letter->subject, PHP_EOL;
}
```
