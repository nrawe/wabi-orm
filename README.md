# WabiORM

WabiORM is a minimalist, (near) zero-config object-relational mapper.

<p align="center">

[![Build Status](https://travis-ci.org/nrawe/wabi-orm.svg?branch=master)](https://travis-ci.org/nrawe/wabi-orm/)

[![Maintainability](https://api.codeclimate.com/v1/badges/ffc2dcd245a296b0f55a/maintainability)](https://codeclimate.com/github/nrawe/wabi-orm/maintainability)

[![Test Coverage](https://api.codeclimate.com/v1/badges/ffc2dcd245a296b0f55a/test_coverage)](https://codeclimate.com/github/nrawe/wabi-orm/test_coverage)
</p>

## Rationale

"Wabi" is a term borrowed from Japanese asethetics which loosely translates as
"deliberately simple in daily living". This is something I miss when working
with databases in PHP.

Don't get me wrong, there are some great libraries like Doctrine and Eloquent
written to solve this problem and all credit to their authors for the fantastic
work they've put in.

However, in probably 80% of what I want to do, they're less simple than I'd
like. Additionally, from working with more functional programming ideas in the
React/JavaScript world, I find myself wanting some of those characteristics on
the server.

Lastly, I caught the train during the MicroPHP years and still look back at
that period fondly. The desire for minimalism and focus on the 80% requirement
stands in direct contrast to the monoliths, as good as they are, which surround
us today.

In all, this is my attempt to find joy in database usage again, and maybe it
will bring that to you, too. Or not ¯\_(ツ)_/¯

## @Glance

```php
<?php

use function WabiORM\{
    belongs_to,
    connect, 
    delete, 
    find_one, 
    has_many, 
    global_read, 
    global_write, 
    mysql, 
    save
};

$connect = connect(mysql($host, $db, $user, $pwd));

global_read($connect);
global_write($connect);

class User {
    public $id;

    public function posts() {
        return has_many(Post::class, $this);
    }
}

class Post {
    public $id;

    public $user_id;

    public $title;

    public $content;

    public function user() {
        return belongs_to(User::class, $this);
    }
}

$newPost = new Post();
$newPost->title = 'My first post';
$newPost->content = 'WabiORM put the fun back into database usage!';

$id = save($newPost);

$post = find_one(Post::class, $id);
$post->title = 'My first post (edited!)';

save($post);
delete($post);

$user = find_one(User::class, 1);

foreach ($user->posts() as $post) {
    echo $post->title, PHP_EOL;
}

```

## Features

1. PDO connection middleware
2. Works with plain classes
3. Simple, powerful query templates

## Install

Installation is handled via [Composer](https://getcomposer.org):

```
$ composer require nrawe/wabi-orm
```

## Test

The library ships with a test suite, see [specs](specs/), which can be run by:

```
$ composer test
```

## Documentation

This section documents the advanced usage.

1. [Using connections](docs/connect.md)
2. [Working with models](docs/models.md)
3. [Working with model relationships](docs/relationships.md)
4. [Writing queries with `q()`](docs/queries.md)

## Roadmap
See [ROADMAP.md](./ROADMAP.md) for details of the planned work.

## License
MIT.
