# Relationships, v2

ActiveRecord systems have a general, slightly DDD inspired approach of having
relationships encapsulated inside of models. WabiORM v1 does the same thing:

```php
class User {

    public function posts() {
        return has_many(Post::class, $this);
    }
}

class Post {

    public function user() {
        return belongs_to(User::class, $this);
    }
}

$user = find_first(User::class);

foreach ($user->posts() as $post) {
    // Do something with the post
}
```

However, this is problematic for performance reasons. For example, the n+1
problem cannot be solved with WabiORM right now and this is not an unrealistic
expectation. For example, say we're building an API endpoint `GET /posts`
which returns an array of posts with their author:

```json
{
    "posts": [
        {
            "id": 1,
            "title": "My awesome post",
            "author": {
                "id": 1,
                "name": "Harold McMillian"
            }
        },
        {
            "id": 2,
            "title": "Following up on 'My awesome post'",
            "author": {
                "id": 1,
                "name": "Harold McMillian"
            }
        }
    ]
}
```

Right now, that might look something like:

```php
function user_dto(User $user): array {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
}

function post_dto(Post $post): array {
    return [
        'id' => $post->id,
        'title' => $post->title,
        'author' => user_dto($post->user()),
    ];
}

$app->get('/posts', function ($req) {
    $posts = find_all(Post::class);

    return ['posts' => map($posts, 'post_dto')];
});
```

The issue with this is that it will not be able to optimise the loading
of the user relationship load without some intervention. Here are some possible
solutions to that problem.

## Solution 1: Using a global data buffer

Taking inspiration from DataLoader:

```php
class Post {
    public function user() {
        return buffer(User::class, $this->user_id, function () {
            return belongs_to(User::class, $this);
        });
    }
}
```

The syntax for this is considerably disruptive to users. As such, we may then
expose a shortcut for this, such as:

```php
class Post {
    public function user() {
        return belongs_to_cached(User::class, $this);
    }
}
```

The syntax change is minor and opt-in (i.e. the use of a cache is explicit).
There is a question around this should be the default behaviour, but I don't
think that would be of benefit currently.

There would be no way for this to be garbage collected until the very end of the
process because it would be buffered in a static scope, unless we provide some
way of exposing the cache.

I'm not too concerned about that point, however, as in an API context, the
goal is to serve the request as fast as possible, so that GC wouldn't really
matter. For other workloads, however, this might be sub-optimal behaviour.


## Solution 2: add a specific function for loading any relationship

For example, keeping the overall syntax the same, but adding an additional step:

```php

class Post {
    public function user() {
        return belongs_to(User::class, $this);
    }
}

// Get a single user back for the given post
$user = find_related($post->user(), $post);

// Allow returning the users for many posts; looses some context
$users = find_releated($post->user(), [$post1, $post2]);
```

What I don't like about this is using an instance of a model to get a
relationship to then pass the model through. As such, this could be re-written
to:

```php
class Post {
    public static function user() {
        return belongs_to(User::class, static);
    }
}

$user = find_related(Post::user(), $post);
```

This is somewhat better, and the `model_info` API can support this change fairly
well. However, it seems... awkward, and out of keeping with the FP vibe.

## Solution 3: tear the ActiveRecord book up

Building on Solution 2, we could just go fully into the FP world. 

```php
function user_dto(User $user): array {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
}

function post_dto(Post $post, array $usersById): array {
    return [
        'id' => $post->id,
        'title' => $post->title,
        'author' => user_dto($usersById[$post->user_id]),
    ];
}

function post_author() {
    return belongs_to(User::class, Post::class);
}

$app->get('/posts', function ($req) {
    $posts = find_all(Post::class);

    $usersById = group(find_related(post_author(), $posts), 'id');

    return [
        'posts' => map($posts, function ($post) use ($usersById) {
            return post_dto($post, $usersById);
        }),
    ];
});
```

This could also make us rethink the data output design more carefully, too:

```php
function user_dto(User $user): array {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
}

function post_dto(Post $post): array {
    return [
        'id' => $post->id,
        'title' => $post->title,
        'authorId' => $post->user_id,
    ];
}

function post_author() {
    return belongs_to(User::class, Post::class);
}

$app->get('/posts', function ($req) {
    $posts = find_all(Post::class);
    $users = find_related(post_author(), $posts);

    return [
        'posts' => map($posts, 'post_dto'),
        'users' => map($users, 'user_dto'),
    ];
});
```

Usage of frontend tools like Redux are made more efficient through this form of
Server-Side normalisation of data. It also reduces the weight of the output by
reducing duplication of data.

The con of this approach is that it isn't going to be to everyone's tastes. As
we can see from the somewhat hostile discussions in GraphQL over the lack of a
wild card, where a wild card is antithetical to its principle of being explicit, 
users do not always grasp the mindset change needed to use a tool. That doesn't
mean its wrong, though...

## Outcome

Solution 3 offers the best overall trade-offs. It allows us to provide one
mechanism to load relationships and to reuse a relationship definition for other
purposes down the line. It allows us to solve the n+1 problem in an explicit
manner and, while it breaks with AR conventions in other systems, it maps
more closely to the core ideas of WabiORM itself.
