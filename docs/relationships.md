# Working with Relationships

WabiORM supports loading relationships between objects in a similar way to
other object-relational mappers, although with a functional aspect:

```php

use function WabiORM\{belongs_to, has_many, has_one, find_related};

class Address {
    public $id;

    public $person_id;
}

class Person {
    public $id;
}

class Letter {
    public $id;

    public $address_id;

    public $person_id;

    public $subject;
}

// Return a single address for the person
$person = find_one(Person::class, 1);
$address = find_related(person_address(), $person);

$address->person()->id === $person->id;

// Return the unqiue addresses for each of the letters
$letters = find_all(Letter::class);
$addresses = find_related(letter_address(), $letters);

foreach ($address->letters() as $letter) {
    echo $letter->subject, PHP_EOL;
}

function address_letters() {
    return has_many(Letter::class, Address::class);
}

function address_person() {
    return belongs_to(Person::class, Address::class);
}

function person_address() {
    return has_one(Address::class, Person::class);
}

function person_letters() {
    return has_many(Letter::class, Person::class);
}

function letter_address() {
    return belongs_to(Address::class, Letter::class);
}

function letter_person() {
    return belongs_to(Person::class, Letter::class);
}

```
