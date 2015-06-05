<?php

namespace Pop\Auth\Test;

use Pop\Auth\Auth;
use Pop\Auth\Adapter;

class AuthLdapTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $ldap = new Adapter\Ldap('ldap.domain', 389, [
            LDAP_OPT_PROTOCOL_VERSION => 3
        ]);
        $this->assertInstanceOf('Pop\Auth\Adapter\Ldap', $ldap);
        $this->assertEquals('ldap.domain', $ldap->getHost());
        $this->assertEquals(389, $ldap->getPort());
        $this->assertEquals(3, $ldap->getOption(LDAP_OPT_PROTOCOL_VERSION));
        $this->assertEquals(1, count($ldap->getOptions()));
        $this->assertTrue(is_resource($ldap->resource()));
        $this->assertEquals(Auth::NOT_VALID, $ldap->authenticate());
    }

}