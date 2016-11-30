<?php

namespace Pop\Auth\Test;

use Pop\Auth\Ldap;

class AuthLdapTest extends \PHPUnit_Framework_TestCase
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
        $this->assertEquals(Ldap::NOT_VALID, $ldap->authenticate('username', 'password'));
    }

}