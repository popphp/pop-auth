<?php

namespace Pop\Auth\Test;

use Pop\Auth\File;
use Pop\Auth\Exception;
use PHPUnit\Framework\TestCase;

class AuthFileTest extends TestCase
{

    public function testConstructor()
    {
        $file = new File(__DIR__ . '/tmp/access-realm.txt', 'domain', '|');
        $this->assertInstanceOf('Pop\Auth\File', $file);
        $this->assertEquals(__DIR__ . '/tmp/access-realm.txt', $file->getFilename());
        $this->assertEquals('|', $file->getDelimiter());
        $this->assertEquals('domain', $file->getRealm());
        $this->assertEquals(File::VALID, $file->authenticate('admin','12admin34'));
        $this->assertEquals(File::VALID, $file->getResult());
        $this->assertTrue($file->isAuthenticated());
        $this->assertEquals('admin', $file->getUsername());
        $this->assertEquals('12admin34', $file->getPassword());
    }

    public function testSetAndGetRealm()
    {
        $file = new File(__DIR__ . '/tmp/access-realm.txt');
        $file->setRealm('domain');
        $this->assertEquals('domain', $file->getRealm());
    }

    public function testSetAndGetDelimiter()
    {
        $file = new File(__DIR__ . '/tmp/access-realm.txt');
        $file->setDelimiter('|');
        $this->assertEquals('|', $file->getDelimiter());
    }

    public function testFileDoesNotExist()
    {
        $this->expectException('Pop\Auth\Exception');
        $file = new File('bad-file.txt');
    }

    public function testVerifyPlaintextFallback()
    {
        $file = new File(__DIR__ . '/tmp/access.txt');
        $this->assertTrue($file->verify('12admin34', '12admin34'));
        $this->assertFalse($file->verify('wrong-password', '12admin34'));
    }

    public function testNeedsRehashWithPlaintextCredential()
    {
        $file = new File(__DIR__ . '/tmp/access.txt');
        $this->assertEquals(File::VALID, $file->authenticate('admin', '12admin34'));
        $this->assertTrue($file->needsRehash());
    }

    public function testNeedsRehashWithStaleBcryptCost()
    {
        $file = new File(__DIR__ . '/tmp/access.txt');
        $this->assertEquals(File::VALID, $file->authenticate('admin_bcrypt', '12admin34'));
        $this->assertTrue($file->needsRehash());
    }

    public function testNeedsRehashFalseForCurrentHash()
    {
        $file = new File(__DIR__ . '/tmp/access.txt');
        $hash = password_hash('secret123', PASSWORD_DEFAULT);
        $this->assertTrue($file->verify('secret123', $hash));
        $this->assertFalse($file->needsRehash());
    }

    public function testNeedsRehashResetsOnSubsequentFailedAttempt()
    {
        $file = new File(__DIR__ . '/tmp/access.txt');
        $this->assertEquals(File::VALID, $file->authenticate('admin', '12admin34'));
        $this->assertTrue($file->needsRehash());

        $this->assertEquals(File::NOT_VALID, $file->authenticate('nobody', 'whatever'));
        $this->assertFalse($file->isAuthenticated());
        $this->assertFalse($file->needsRehash());
    }

    public function testAuthenticateSucceedsWhenRealmConfiguredButFileHasNoRealmField()
    {
        // Asymmetric by design of the matching logic: a realm-configured adapter still
        // falls through to the 2-field branch for a line with no realm field, so the
        // realm check is only enforced against 3-field lines, not required of every line.
        $file = new File(__DIR__ . '/tmp/access.txt', 'domain');
        $this->assertEquals(File::VALID, $file->authenticate('admin', '12admin34'));
    }

    public function testAuthenticateFailsWhenNoRealmConfiguredButFileHasRealmField()
    {
        $file = new File(__DIR__ . '/tmp/access-realm.txt');
        $this->assertEquals(File::NOT_VALID, $file->authenticate('admin', '12admin34'));
        $this->assertFalse($file->isAuthenticated());
    }

    public function testAuthenticateThrowsWhenFileUnreadable()
    {
        $filename = __DIR__ . '/tmp/access-unreadable.txt';
        file_put_contents($filename, "admin:12admin34\n");
        chmod($filename, 0000);

        try {
            $this->expectException(Exception::class);
            $file = new File($filename);
            $file->authenticate('admin', '12admin34');
        } finally {
            chmod($filename, 0644);
            unlink($filename);
        }
    }

    public function testAuthenticateReturnsNotValidWhenPasswordIsNull()
    {
        $file = new File(__DIR__ . '/tmp/access.txt');
        $this->assertEquals(File::NOT_VALID, $file->authenticate('admin', null));
        $this->assertFalse($file->isAuthenticated());
    }

}