# Writing Queries with Q

WabiORM does not use or provide a query builder. Query builders can sometimes
be exactly the solution needed, and there is no blocker to using them with this
library, but from the libraries perspective this would largely be overkill.

What is provided instead is a helper function called `q()`, which provides some
shortcuts for common problems when working with plain SQL strings. To use this,
you specify your query as a template, with some special template tags embedded.

This gets passed to `q()`, which replaces these tags. General usage looks like:

```php
use function WabiORM\q;

[$query, $params] = q($query, $data);

// Usage with connect()
$result = $executor(...q($query, $data));
```

Currently, the scope of this is limited to the libraries requirements, but
sensible extensions - with a clear problem/solution - will be considered.

## Condition shortcuts

**Problem:** when working with queries, it's difficult to handle both single
bindings and arrays of bindings, especially with repeating values.

**Solution:** `q()` provides template tags to abstract comparisons, handling
both repeating bindings and single/array value bindings:

#### Equality, single value
```php
// query: select * from table where a = ? 
// params: [1]
q('select * from table where {=a}', ['a' => 1]);
```

#### Inequality, single value
```php
// query: select * from table where a != ? 
// params: [1]
q('select * from table where {!a}', ['a' => 1]);
```

#### Equality, multiple values
```php
// query: select * from table where a in (?, ?)
// params: [1, 2]
q('select * from table where {=a}', ['a' => [1, 2]]);
```

#### Inequality, multiple values
```php
// query: select * from table where a not in (?, ?)
// params: [1, 2]
q('select * from table where {!a}', ['a' => [1, 2]]);
```

#### Greater than, single value
```php
// query: select * from table where a > ?
// params: [1]
q('select * from table where {>a}', ['a' => 1]);
```

#### Greater than, multiple values
```php
// query: select * from table where (a > ? or a > ?)
// params: [1, 2]
q('select * from table where {>a}', ['a' => [1, 2]]);
```

#### Greater than or equal to, single value
```php
// query: select * from table where a >= ?
// params: [1]
q('select * from table where {>=a}', ['a' => 1]);
```

#### Greater than or equal to, multiple values
```php
// query: select * from table where (a >= ? or a >= ?)
// params: [1, 2]
q('select * from table where {>=a}', ['a' => [1, 2]]);
```

#### Less than, single value
```php
// query: select * from table where a < ?
// params: [1]
q('select * from table where {>a}', ['a' => 1]);
```

#### Less than, multiple values
```php
// query: select * from table where (a < ? or a < ?)
// params: [1, 2]
q('select * from table where {<a}', ['a' => [1, 2]]);
```

#### Less than or equal to, single value
```php
// query: select * from table where a <= ?
// params: [1]
q('select * from table where {<=a}', ['a' => 1]);
```

#### Less than or equal to, multiple values
```php
// query: select * from table where (a <= ? or a <= ?)
// params: [1, 2]
q('select * from table where {<=a}', ['a' => [1, 2]]);
```

#### Starts like
```php
// query: select * from table where a like ?
// params: ['%b']
q('select * from table where {%a}', ['a' => 'b']);
```

#### Contains
```php
// query: select * from table where a like ?
// params: ['%b%']
q('select * from table where {%a%}', ['a' => 'b']);
```

#### Ends like
```php
// query: select * from table where a like ?
// params: ['b%']
q('select * from table where {a%}', ['a' => 'b']);
```

#### I know what I'm doing, just give me the value
```php
// query: select * from table where myCondition = ? 
// params: [1]
q('select * from table where myCondition = {a}', ['a' => 1]);

// query: insert into table values(?, ?) 
// params: [1, 2]
q('insert into table values({a})', ['a' => [1, 2]);
```

## Raw values

**Problem:** sometimes you need to embed a raw value into a query.

**Solution:** while you can use PHP String Interpolation, `q()` provides a
helper for this to work inside the template itself:

```php
// query: select * from name
// params: []
q('select * from {*table}', ['table' => 'name']);

// query: insert into table (a, b)
// params: []
q('insert into table ({*fields})', ['fields' => ['a', 'b']]);
```
