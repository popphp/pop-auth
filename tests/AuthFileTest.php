<?php

namespace Pop\Auth\Test;

use Pop\Auth\File;

class AuthFileTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $file = new File(__DIR__ . '/tmp/access-realm.txt', 'domain', '|');
        $this->assertInstanceOf('Pop\Auth\File', $file);
        $this->assertEquals(__DIR__ . '/tmp/access-realm.txt', $file->getFilename());
        $this->assertEquals('|', $file->getDelimiter());
        $this->assertEquals('domain', $file->getRealm());
        $this->assertEquals(File::VALID, $file->authenticate('admin','12admin34'));
    }

    public function testFileDoesNotExist()
    {
        $this->expectException('Pop\Auth\Exception');
        $file = new File('bad-file.txt');
    }

}