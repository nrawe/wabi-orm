# Working with Models

A model in WabiORM is any plain class. By default, the framework infers the
information about how to related the class to the database, but you can add
annotations to instruct it, too.

```php

/**
 * By default, the framework will infer this classes records will be in a
 * `posts` table with an `id` column.
 */ 
class Post {

}

class MyModel {
    /**
     * This tells the framework that the primary key for the model is something
     * other than `id`.
     */
    public function withPrimaryKey(): string {
        return 'guid';
    }

    /**
     * This tells the framework to look for MyModel instances in
     * `a_custom_table`.
     */
    public function withTableName(): string {
        return 'a_custom_table';
    }
}
```

Because any plain class can work with the framework, any methods and properties
will still work. Because this wraps PDO, any public properties will be
hydrated for you automatically, although you don't need to define them before
use.
