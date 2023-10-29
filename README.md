pop-auth
========

[![Build Status](https://github.com/popphp/pop-auth/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-auth/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-auth)](http://cc.popphp.org/pop-auth/)

[![Join the chat at https://popphp.slack.com](https://media.popphp.org/img/slack.svg)](https://popphp.slack.com)
[![Join the chat at https://discord.gg/D9JBxPa5](https://media.popphp.org/img/discord.svg)](https://discord.gg/D9JBxPa5)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Using a File](#using-a-file)
* [Using a Database](#using-a-databse)
* [Using HTTP](#using-http)
* [Using LDAP](#using-ldap)

Overview
--------
`pop-auth` provides adapters to authenticate users via different authentication sources.
The adapters share the same interface and are interchangeable. The available available
adapters are:

- File
- Database
- HTTP
- LDAP

`pop-auth` is a component of the [Pop PHP Framework](http://www.popphp.org/).

Install
-------

Install `pop-auth` using Composer.

    composer require popphp/pop-auth

Or, require it in your composer.json file

    "require": {
        "popphp/pop-auth" : "^4.0.0"
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

If you need to reference the authentication attempt result at a later time in the application, 
you can call `isAuthenticated()`:

```php
var_dump($auth->isAuthenticated()); // bool
```

[Top](#pop-auth)

Using a File
------------

Using the file adapter, you would need to create we use a file containing a colon-delimited
list of usernames and passwords or, preferably, password hashes:

```text
testuser1:PASSWORD_HASH1
testuser2:PASSWORD_HASH2
testuser3:PASSWORD_HASH3
```

```php
use Pop\Auth;

$auth = new Auth\File('/path/to/.htmyauth');
$auth->authenticate('testuser1', 'password'); // Return int

if ($auth->isAuthenticated()) { } // Returns bool
```

[Top](#pop-auth)

Using a Database
----------------

Using the table adapter, you would need to create a table in a database. There would need to be a
correlating table class  that extends 'Pop\Db\Record' (for more on this, visit the `pop-db` component.)

For simplicity, the table has been named `MyApp\Table\Users` and has a column called 'username' and
a column called 'password', but those column names can be changed.

```php
use Pop\Auth;

$auth = new Auth\Table('MyApp\Table\Users');
$auth->authenticate('admin', 'password'); // int

if ($auth->isAuthenticated()) { } // bool
```

If the username/password fields are called something different in the table, that can be changed:

```php
use Pop\Auth;

$auth = new Auth\Table('MyApp\Table\Users');
$auth->setUsernameField('user_name')
    ->setPasswordField('password_hash');

$auth->authenticate('admin', 'password'); // int

if ($auth->isAuthenticated()) { } // bool
```

[Top](#pop-auth)

Using HTTP
----------

Using the HTTP adapter, the user can send an authentication request over HTTP to a remote server.
It will utilize the `Pop\Http\Client` and its supporting classes from the `pop-http` component.
The following example will set the username and password at POST data in the payload.

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

$auth = new Http(
    new Client('https://www.domain.com/auth', ['method' => 'post']),
    Auth::createBasic('admin', 'password')
);

$auth->authenticate('admin', 'password'); // Returns int

if ($auth->isAuthenticated()) { } // Returns bool
```

The following example will use a bearer token authorization header:

```php
use Pop\Auth\Http;
use Pop\Http\Client;
use Pop\Http\Auth;

$auth = new Http(
    new Client('https://www.domain.com/auth', ['method' => 'post']),
    Auth::createBearer('AUTH_TOKEN')
);

$auth->authenticate('admin', 'password');

if ($auth->isAuthenticated()) { } // Returns true
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

Using LDAP
----------

Using the LDAP adapter, the user can send an authentication request using LDAP to a remote server.
The user can set the port and other various options that may be necessary to communicate with the
LDAP server.

```php
use Pop\Auth;

$auth = new Auth\Ldap('ldap.domain', 389, [LDAP_OPT_PROTOCOL_VERSION => 3]);
$auth->authenticate('admin', 'password');

if ($auth->isAuthenticated()) { } // Returns true
```

[Top](#pop-auth)
