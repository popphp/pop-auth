pop-auth
========

[![Build Status](https://github.com/popphp/pop-auth/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-auth/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-auth)](http://cc.popphp.org/pop-auth/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Handling Exceptions](#handling-exceptions)
* [Using a File](#using-a-file)
* [Using a Database](#using-a-database)
* [Using HTTP](#using-http)
* [Getting the User](#getting-the-user)
* [Rehashing Passwords](#rehashing-passwords)

Overview
--------
`pop-auth` provides adapters to authenticate users via different authentication sources.
The adapters share the same interface and are interchangeable. The available
adapters are:

- File
- Database
- HTTP

`pop-auth` is a component of the [Pop PHP Framework](http://www.popphp.org/).

Install
-------

Install `pop-auth` using Composer.

    composer require popphp/pop-auth

Or, require it in your composer.json file

    "require": {
        "popphp/pop-auth" : "^5.0.0"
    }

[Top](#pop-auth)

Quickstart
----------

To verify an authentication attempt, create a new auth object pointed at its authentication source.
From there, you can attempt to call the `authenticate()` with a username and password.

```php
use Pop\Auth;

$auth = new Auth\File('/path/to/.htmyauth');

if ($auth->authenticate('admin', 'password')) {
    // User is authenticated
} else {
    // Handle failed authentication attempt
}
```

If you need to reference the same authentication attempt result at a later time in the application, 
you can call `isAuthenticated()`:

```php
var_dump($auth->isAuthenticated()); // Returns bool
```

[Top](#pop-auth)

Handling Exceptions
--------------------

`authenticate()` returning `0` means the credentials were checked and didn't match — that's a normal, expected
outcome, not an error. It can also throw `Pop\Auth\Exception`, but only when the adapter can't perform the check
at all: a missing or unreadable file, a bad database table class or connection, or no HTTP client configured (or
a transport-level failure sending the request). Wrap `authenticate()` in a
try/catch to handle both cases:

```php
use Pop\Auth;

$auth = new Auth\File('/path/to/.htmyauth');

try {
    if ($auth->authenticate('admin', 'password')) {
        // User is authenticated
    } else {
        // Credentials didn't match
    }
} catch (Auth\Exception $e) {
    // The adapter couldn't perform the check at all
}
```

[Top](#pop-auth)

Using a File
------------

Using the file adapter, you need to create a file containing a colon-delimited list of
usernames and passwords or, preferably, password hashes:

```text
testuser1:PASSWORD_HASH1
testuser2:PASSWORD_HASH2
testuser3:PASSWORD_HASH3
```

```php
use Pop\Auth;

$auth = new Auth\File('/path/to/.htmyauth');
$auth->authenticate('testuser1', 'password'); // Returns int

if ($auth->isAuthenticated()) { } // Returns bool
```

The file adapter also supports realm-scoped entries and a custom field delimiter. When a
realm is set, matching lines must have 3 fields (`username:realm:hash`) with both the
username *and* the realm matching:

```text
testuser1:example.com:PASSWORD_HASH1
testuser2:example.com:PASSWORD_HASH2
```

```php
use Pop\Auth;

$auth = new Auth\File('/path/to/.htmyauth', 'example.com');
$auth->authenticate('testuser1', 'password'); // Returns int
```

A different delimiter can be set as the third constructor argument, e.g.
`new Auth\File('/path/to/.htmyauth', 'example.com', '|')` for pipe-delimited entries.

[Top](#pop-auth)

Using a Database
----------------

Using the table adapter, you would need to create a table in a database that stores the users.
There would need to be a correlating table class that extends `Pop\Db\Record` (for more on this,
visit the `pop-db` component.)

For simplicity, the table class has been named `MyApp\Table\Users` and has a column called
`username` and a column called `password`, but those column names can be changed.

```php
use Pop\Auth;

$auth = new Auth\Table('MyApp\Table\Users');
$auth->authenticate('admin', 'password'); // Returns int

if ($auth->isAuthenticated()) { } // Returns bool
```

If the username/password fields are called something different in the table, that can be changed:

```php
use Pop\Auth;

$auth = new Auth\Table('MyApp\Table\Users');
$auth->setUsernameField('user_name')
    ->setPasswordField('password_hash');

$auth->authenticate('admin', 'password'); // Returns int

if ($auth->isAuthenticated()) { } // Returns bool
```

[Top](#pop-auth)

Using HTTP
----------

Using the HTTP adapter, the user can send an authentication request over HTTP to a remote server.
It will utilize the `Pop\Http\Client` and its supporting classes from the `pop-http` component.
The following example will set the username and password as POST data in the payload.

The `Http` constructor also accepts a `Pop\Http\Auth` object directly instead of building it into the
`Client`, e.g. `new Http($client, $auth)` — but the `Client` must be passed first. Passing the `Auth`
object before the `Client` silently drops it rather than raising an error.

```php
use Pop\Auth\Http;
use Pop\Http\Client;

$auth = new Http(new Client('https://www.domain.com/auth', ['method' => 'post']));
$auth->authenticate('admin', 'password'); // Returns int

if ($auth->isAuthenticated()) { } // Returns bool
```

The following example will use a basic authorization header:

```php
use Pop\Auth\Http;
use Pop\Http\Client;
use Pop\Http\Auth;

$client = new Client(
    'https://www.domain.com/auth', ['method' => 'post'],
    Auth::createBasic('admin', 'password')
); 

$auth = new Http($client);
$auth->authenticate('admin', 'password'); // Returns int

if ($auth->isAuthenticated()) { } // Returns bool
```

The following example will use a bearer token authorization header:

```php
use Pop\Auth\Http;
use Pop\Http\Client;
use Pop\Http\Auth;

$client = new Client(
    'https://www.domain.com/auth', ['method' => 'post'],
    Auth::createBearer('AUTH_TOKEN')
);

$auth = new Http($client);
$auth->authenticate('admin', 'password'); // Returns int

if ($auth->isAuthenticated()) { } // Returns bool
```

Like the Table adapter, if the username/password fields need to be set to something different
to meet the requirements of the HTTP server, you can do that:

```php
use Pop\Auth\Http;
use Pop\Http\Client;

$auth = new Http(new Client('https://www.domain.com/auth', ['method' => 'post']));
$auth->setUsernameField('user_name')
    ->setPasswordField('password_hash');

$auth->authenticate('admin', 'password'); // Returns int

if ($auth->isAuthenticated()) { } // Returns bool
```

[Top](#pop-auth)

Getting the User
----------------

Both the table and HTTP adapters have a method that allow you to get any possible user data that
may have been returned. That method is `getUser()`:

```php
use Pop\Auth;

$auth = new Auth\Table('MyApp\Table\Users');
$auth->authenticate('admin', 'password'); // Returns int

if ($auth->isAuthenticated()) {
    $user = $auth->getUser();
}
```

This allows you access to the authenticated user's data without having to make an additional request. 

[Top](#pop-auth)

Rehashing Passwords
-------------------

After a successful authentication, you can check whether the stored hash should be upgraded (e.g. it was stored
as plaintext, or was hashed at a now-outdated cost):

```php
use Pop\Auth;

$auth = new Auth\File('/path/to/.htmyauth');
$auth->authenticate('admin', 'password');

if ($auth->isAuthenticated() && $auth->needsRehash()) {
    $newHash = password_hash('password', PASSWORD_DEFAULT);
    // persist $newHash into the file/database yourself — pop-auth never writes to storage
}
```

This is only meaningful for the File and Table adapters, since they're the ones that compare a
submitted password against a stored hash. The HTTP adapter never compares hashes directly —
`needsRehash()` is always `false` on it.

[Top](#pop-auth)
