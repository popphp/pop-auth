<?php

namespace Pop\Auth\Test;

use Pop\Auth\Table;
use Pop\Db;
use PHPUnit\Framework\TestCase;

class AuthTableTest extends TestCase
{

    public function testConstructor()
    {
        $db = Db\Db::sqliteConnect(['database' => __DIR__ . '/tmp/access.sqlite']);
        Db\Record::setDb($db);

        $table = new Table('Pop\Auth\Test\TestAsset\Users');
        $this->assertInstanceOf('Pop\Auth\Table', $table);
        $this->assertEquals('Pop\Auth\Test\TestAsset\Users', $table->getTable());
        $this->assertEquals('username', $table->getUsernameField());
        $this->assertEquals('password', $table->getPasswordField());
        $this->assertEquals(Table::VALID, $table->authenticate('admin', '12admin34'));
        $this->assertInstanceOf('Pop\Db\Record\AbstractRecord', $table->getUser());
    }

    public function testSetAndGetTable()
    {
        $table = new Table('Pop\Auth\Test\TestAsset\Users');
        $table->setTable('Pop\Auth\Test\TestAsset\Users2');
        $this->assertEquals('Pop\Auth\Test\TestAsset\Users2', $table->getTable());
    }

    public function testSetAndGetUsernameField()
    {
        $table = new Table('Pop\Auth\Test\TestAsset\Users');
        $table->setUsernameField('username2');
        $this->assertEquals('username2', $table->getUsernameField());
    }

    public function testSetAndGetPasswordField()
    {
        $table = new Table('Pop\Auth\Test\TestAsset\Users');
        $table->setPasswordField('password2');
        $this->assertEquals('password2', $table->getPasswordField());
    }

}