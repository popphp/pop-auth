<?php

namespace Pop\Auth\Test;

use Pop\Auth\Auth;
use Pop\Auth\Adapter;

class AuthFileTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $file = new Adapter\File(__DIR__ . '/tmp/access-realm.txt');
        $this->assertInstanceOf('Pop\Auth\Adapter\File', $file);
        $file->setFilename(__DIR__ . '/tmp/access-realm.txt')
             ->setDelimiter('|')
             ->setRealm('domain')
             ->setUsername('admin')
             ->setPassword('12admin34');
        $this->assertEquals(__DIR__ . '/tmp/access-realm.txt', $file->getFilename());
        $this->assertEquals('|', $file->getDelimiter());
        $this->assertEquals('domain', $file->getRealm());
        $this->assertEquals(Auth::VALID, $file->authenticate());
    }

    public function testFileDoesNotExist()
    {
        $this->setExpectedException('Pop\Auth\Adapter\Exception');
        $file = new Adapter\File('bad-file.txt');
    }

}