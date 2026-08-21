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
* [Using a JWT](#using-a-jwt)
* [Getting the User](#getting-the-user)
* [Rehashing Passwords](#rehashing-passwords)

Overview
--------
`pop-auth` provides adapters to authenticate users via different authentication sources.
The adapters share the same interface and are interchangeable. The available
adapters are:

- File
- JWT

`pop-auth` is a component of the [Pop PHP Framework](http://www.popphp.org/).

> Database-backed authentication (matching a username/password against a `Pop\Db\Record`-backed table) has
> moved to the `pop-db` component, in favor of a tighter coupling with the DB layer it depends on. See
> `pop-db`'s documentation for the replacement.

> Remote/delegated authentication (forwarding credentials to a remote HTTP endpoint and checking the response)
> is no longer a dedicated adapter here — use `pop-http`'s `Client` and `Auth` classes directly.

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
at all: a missing or unreadable file, or malformed/unusable key material for the JWT adapter. Wrap
`authenticate()` in a try/catch to handle both cases:

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

Using a JWT
-----------

Using the JWT adapter, you authenticate a bearer token by verifying its signature and claims — there's no
network call and no stored username/password, just the token itself and the key material to check it against.

The algorithm is fixed at construction and is never read from the token — pass the shared secret for `HS256`,
or a PEM-encoded public key for `RS256`/`ES256`:

```php
use Pop\Auth\Jwt;

$auth = new Jwt('HS256', $secret);
$auth->authenticate($token); // Returns int

if ($auth->isAuthenticated()) { } // Returns bool
```

```php
use Pop\Auth\Jwt;

$auth = new Jwt('RS256', $publicKeyPem);
$auth->authenticate($token); // Returns int
```

`exp` and `nbf` claims, when present on the token, are always checked (with an optional leeway, in seconds, to
absorb clock skew between issuer and verifier). `aud` and `iss` are opt-in — only checked if you configure them:

```php
use Pop\Auth\Jwt;

$auth = new Jwt('HS256', $secret);
$auth->setAudience('my-api')
    ->setIssuer('https://auth.example.com')
    ->setLeeway(30);

$auth->authenticate($token); // Returns int
```

[Top](#pop-auth)

Getting the User
----------------

The JWT adapter has a method that allows you to get the decoded claims from a successfully verified token.
That method is `getUser()`:

```php
use Pop\Auth\Jwt;

$auth = new Jwt('HS256', $secret);
$auth->authenticate($token); // Returns int

if ($auth->isAuthenticated()) {
    $claims = $auth->getUser();
}
```

This allows you access to the token's claims without decoding it yourself.

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

This is only meaningful for the File adapter, since it's the one that compares a submitted
password against a stored hash. The JWT adapter verifies a signature instead of a hash —
`needsRehash()` is always `false` on it.

[Top](#pop-auth)
