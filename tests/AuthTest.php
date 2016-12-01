<?php

namespace Pop\Auth\Test;

use Pop\Auth\File;

class AuthTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $file = new File(__DIR__ . '/tmp/access.txt');
        $file->authenticate('admin_bcrypt', '12admin34');
        $this->assertEquals(1, $file->getResult());
        $this->assertTrue($file->isValid());
        $this->assertEquals('admin_bcrypt', $file->getUsername());
        $this->assertEquals('12admin34', $file->getPassword());
    }

}