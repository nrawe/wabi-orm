<?php

class Address {
    public $id;
    public $line1;
    public $line2;
    public $line3;
    public $line4;
}

class Person {
    public $id;
    public $first_name;
    public $last_name;
    public $address_id;
}


describe('Integration tests', function () {
    it('allows connection', function () {
        WabiORM\global_read(connection());
        WabiORM\global_write(connection());
    });

    it('allows for getting a record', function () {
        $address1 = WabiORM\find_first(Address::class);
        $address2 = WabiORM\find_one(Address::class, $address1->id);

        expect($address1->id)->toEqual($address2->id);
    });

    it('it allows for getting a relationship', function () {
        $person = WabiORM\find_first(Person::class);
        $address = WabiORM\find_related(person_address(), $person->id);

        expect($person->address_id)->toEqual($address->id);

        $person2 = WabiORM\find_related(address_person(), $address->id);

        expect($person2)->toBeAnInstanceOf(Person::class);
    });

    it('creates, saves and deletes records', function () {
        $person = new Person();
        $person->first_name = 'Tom';
        $person->last_name = 'Jones';
        
        $id = WabiORM\save($person);
        $person->id = $id;

        expect(WabiORM\find_one(Person::class, $id))
            ->toBeAnInstanceOf(Person::class);

        WabiORM\delete($person);

        expect(WabiORM\find_one(Person::class, $id))->toEqual(null);
    });
});

function connection() {
    return WabiORM\connect(WabiORM\sqlite(__DIR__ . '/db.sq3'));
}

function address_person() {
    return WabiORM\belongs_to(Person::class, Address::class);
}

function person_address() {
    return WabiORM\has_one(Person::class, Address::class);
}
