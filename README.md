pop-auth
========

[![Build Status](https://travis-ci.org/popphp/pop-auth.svg?branch=master)](https://travis-ci.org/popphp/pop-auth)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-auth)](http://cc.popphp.org/pop-auth/)

OVERVIEW
--------
`pop-auth` provides integrated adapters to authenticate users with a file, a database,
over HTTP or with a LDAP server. It also includes support for authenticating using
encrypted passwords.

`pop-auth` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-auth` using Composer.

    composer require popphp/pop-auth

BASIC USAGE
-----------

### Authenticate using a file

For this example, we use a file called '.htmyauth' containing a colon-delimited
list of usernames and passwords (normally, we wouldn't use clear text):

    admin:12admin34
    editor:12edit34
    reader:12read34

```php
use Pop\Auth;

$auth = new Auth\File('/path/to/.htmyauth');
$auth->authenticate('admin', '12admin34');

if ($auth->isValid()) { } // Returns true
```

### Authenticate using a table in a database

For this example, there is a table in a database called 'users' and a correlating table class
called 'MyApp\Users' that extends 'Pop\Db\Record' (for more on this, visit the Pop Db component.)

For simplicity, the table has a column called 'username' and a column called 'password',
but those field names can be changed.

```php
use Pop\Auth;

$auth = new Auth\Table('MyApp\Users');

// Attempt #1
$auth->authenticate('admin', 'bad-password');

// Returns false because the value of the hashed attempted
// password does not match the hash in the database
if ($auth->isValid()) { }

// Attempt #2
$auth->authenticate('admin', '12admin34');

// Returns true because the value of the hashed attempted
// password matches the hash in the database
if ($auth->isValid()) { }
```

### Authenticate using HTTP

In this example, the user can simply authenticate using a remote server over HTTP.
Based on the headers received from the initial request, the Http adapter will
auto-detect most things, like the the auth type (Basic or Digest), content encoding, etc.

```php
use Pop\Auth;

$auth = new Auth\Http('https://www.domain.com/auth', 'post');
$auth->authenticate('admin', '12admin34');

if ($auth->isValid()) { } // Returns true
```

### Authenticate using LDAP

Again, in this example, the user can simply authenticate using a remote server, but this
time, using LDAP. The user can set the port and other various options that may be necessary
to communicate with the LDAP server.

```php
use Pop\Auth;

$auth = new Auth\Ldap('ldap.domain', 389, [LDAP_OPT_PROTOCOL_VERSION => 3]);
$auth->authenticate('admin', '12admin34');

if ($auth->isValid()) { } // Returns true
```
