<?php

namespace Pop\Auth\Test;

use Pop\Auth\Ldap;
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
        $this->assertEquals(Ldap::NOT_VALID, $ldap->authenticate('username', 'password'));
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