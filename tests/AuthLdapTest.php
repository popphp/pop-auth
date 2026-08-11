<?php

namespace Pop\Auth\Test;

use Pop\Auth\Ldap;
use Pop\Auth\Exception;
use PHPUnit\Framework\TestCase;

class AuthLdapTest extends TestCase
{

    public function testConstructor()
    {
        $ldap = new Ldap('', 389, [
            LDAP_OPT_PROTOCOL_VERSION => 3
        ]);
        $this->assertInstanceOf('Pop\Auth\Ldap', $ldap);
        $this->assertEquals('', $ldap->getHost());
        $this->assertEquals(389, $ldap->getPort());
        $this->assertEquals(3, $ldap->getOption(LDAP_OPT_PROTOCOL_VERSION));
        $this->assertEquals(1, count($ldap->getOptions()));
        $this->assertFalse(is_resource($ldap->resource()));
        $this->assertNull($ldap->getResource());
    }

    public function testAuthenticateAttemptsBindWhenResourceExists()
    {
        // Port 1 is a closed local port: ldap_connect() is lazy (no real connection is
        // made until an operation is attempted), so this gets a real resource, and the
        // subsequent ldap_bind() fails fast (connection refused) instead of hanging,
        // exercising the full bind-attempt path without a real LDAP server.
        $ldap = new Ldap('127.0.0.1', 1);
        $this->assertNotNull($ldap->getResource());
        $this->assertEquals(Ldap::NOT_VALID, $ldap->authenticate('username', 'password'));
        $this->assertFalse($ldap->isAuthenticated());
    }

    public function testAuthenticateThrowsWithNoResource()
    {
        $ldap = new Ldap('', 389, [
            LDAP_OPT_PROTOCOL_VERSION => 3
        ]);
        $this->expectException(Exception::class);
        $ldap->authenticate('username', 'password');
    }

    public function testSetAndGetHost()
    {
        $ldap = new Ldap('', 389, [
            LDAP_OPT_PROTOCOL_VERSION => 3
        ]);
        $ldap->setHost('localhost');
        $this->assertEquals('localhost', $ldap->getHost());
    }

    public function testSetAndGetPort()
    {
        $ldap = new Ldap('', 389, [
            LDAP_OPT_PROTOCOL_VERSION => 3
        ]);
        $ldap->setPort(390);
        $this->assertEquals(390, $ldap->getPort());
    }

}