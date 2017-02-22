<?php

namespace Pop\Auth\Test;

use Pop\Auth\Table;
use Pop\Db;

class AuthTableTest extends \PHPUnit_Framework_TestCase
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


}