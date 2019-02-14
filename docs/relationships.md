# Working with Relationships

WabiORM supports loading relationships between objects in a similar way to
other object-relational mappers:

```php

use function WabiORM\{belongs_to, has_one};

class Address {
    public $id;

    public $person_id;

    public function person() {
        return belongs_to(Person::class, $this);
    }
}

class Person {

    public $id;

    public function address() {
        return has_one(Address::class, $this);
    }
}

$person = find_one(Person::class, 1);
$address = $person->address();

$address->person()->id === $person->id;
```

