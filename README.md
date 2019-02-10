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

## Basic Usage

This section details how to get up and running with WabiORM.

### Install

Installation is handled via [Composer](https://getcomposer.org):

```
$ composer require nrawe/wabi-orm
```

### The Connection

```php
use function WabiORM\{connect, echo_query, middleware, mysql};

// By default, you don't have to do anything special...
$execute = connect($pdo);

// ... But you can wrap query execution with middleware if you want...
$execute = connect($pdo, [echo_query()]);

// ... And you can use a factory for the connection, too.
$execute = connect(mysql('localhost', 'root', 'username', 'password'));

// Use the returned function to execute queries with bound parameters.
$result = $execute($query, $params);

// You can set this executor function globally, for convenience.
use function WabiORM\{global_read, global_write};

global_read($execute);

assert($execute === global_read());
```

### The Model

```php
use function WabiORM\{find_one, find_many};

/**
 * Any plain class can be used as a model, without additional composition or
 * inheritence.
 */
class User {
    public $id;

    public $type;

    public function isAdmin(): bool {
        return $this->type === 'admin';
    }
}

// You can then use one of the pre-made finders...
$user = find_one(User::class, 1);
$admins = find_many(User::class, ['type' => 'admin']);

// By default, these use the global connections, but can be given a connection
$user = find_one(User::class, 1, $execute);

if (! $user) {
    die("Oh, Snap. Not today I'm afraid...");
}

// As you'd expect, what you get is an instance of the class back:
if ($user instanceof User) {
    $user->isAdmin();
}
```

### The Relationships

```php
use function WabiORM\{belongs_to, find_one, has_many};

class User {
    public $id;

    public function posts() {
        return has_many(Post::class, $this);
    }
}

class Post {
    public $id;

    public $user_id;

    public function user() {
        return belongs_to(User::class, $this);
    }
}

// 
$user = find_one(User::class, 1);

// 
$posts = $user->posts();
```

## Advanced Usage

The following sections detail some of the more advanced parts of the library,
which you can probably not know about in most cases.


## Roadmap
See [ROADMAP.md](./ROADMAP.md) for details of the planned work.

## License
MIT.
