<?php

namespace Pop\Auth\Test;

use Pop\Auth\Auth;
use Pop\Auth\Adapter;

class AuthTableTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $table = new Adapter\Table('Pop\Auth\Test\TestAsset\Users');
        $table->setUsernameField('username')
              ->setPasswordField('password')
              ->setUsername('admin')
              ->setPassword('12admin34');
        $this->assertInstanceOf('Pop\Auth\Adapter\Table', $table);
        $this->assertEquals('Pop\Auth\Test\TestAsset\Users', $table->getTable());
        $this->assertEquals('username', $table->getUsernameField());
        $this->assertEquals('password', $table->getPasswordField());
        $this->assertEquals(Auth::VALID, $table->authenticate());
        $this->assertInstanceOf('Pop\Auth\Test\TestAsset\Users', $table->getUser());
    }


}