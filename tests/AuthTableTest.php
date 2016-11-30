<?php

namespace Pop\Auth\Test;

use Pop\Auth\Table;

class AuthTableTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $table = new Table('Pop\Auth\Test\TestAsset\Users');
        $this->assertInstanceOf('Pop\Auth\Table', $table);
        $this->assertEquals('Pop\Auth\Test\TestAsset\Users', $table->getTable());
        $this->assertEquals('username', $table->getUsernameField());
        $this->assertEquals('password', $table->getPasswordField());
        $this->assertEquals(Table::VALID, $table->authenticate('admin', '12admin34'));
        $this->assertInstanceOf('Pop\Db\Record\Result', $table->getUser());
    }


}