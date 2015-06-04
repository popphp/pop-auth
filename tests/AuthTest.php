<?php

namespace Pop\Auth\Test;

use Pop\Auth\Auth;
use Pop\Auth\Adapter;

class AuthTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $auth = new Auth(new Adapter\File(__DIR__ . '/tmp/access.txt'));
        $this->assertInstanceOf('Pop\Auth\Auth', $auth);
        $this->assertInstanceOf('Pop\Auth\Adapter\File', $auth->adapter());
        $auth->authenticate('admin', '12admin34');
        $this->assertTrue($auth->isValid());
        $this->assertEquals(Auth::VALID, $auth->getResult());
    }

    public function testSetUserPass()
    {
        $auth = new Auth(new Adapter\File(__DIR__ . '/tmp/access.txt'));
        $auth->setUsername('admin');
        $auth->setPassword('12admin34');
        $this->assertEquals('admin', $auth->getUsername());
        $this->assertEquals('12admin34', $auth->getPassword());
        $auth->authenticate();
        $this->assertTrue($auth->isValid());
        $this->assertEquals(Auth::VALID, $auth->getResult());
    }

}