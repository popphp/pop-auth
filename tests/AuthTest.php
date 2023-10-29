<?php

namespace Pop\Auth\Test;

use Pop\Auth\File;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{

    public function testConstructor()
    {
        $file = new File(__DIR__ . '/tmp/access.txt');
        $file->authenticate('admin_bcrypt', '12admin34');
        $this->assertEquals(1, $file->getResult());
        $this->assertTrue($file->isAuthenticated());
        $this->assertEquals('admin_bcrypt', $file->getUsername());
        $this->assertEquals('12admin34', $file->getPassword());
    }

}