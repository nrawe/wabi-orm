# WabiORM

WabiORM is a minimalist, (near) zero-config object-relational mapper.

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

use function WabiORM\{connect, find_one, global_read, mysql};

$connect = connect(mysql($host, $db, $user, $pwd));

global_read($connect);

class Post {
    public $id;

    public $title;

    public $content;
}



$post = find_one(Post::class, 1);

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
3. [Writing queries with `q()`](docs/queries.md)

## Roadmap
See [ROADMAP.md](./ROADMAP.md) for details of the planned work.

## License
MIT.
