<?php

namespace Pop\Auth\Test;

use Pop\Auth\Auth;
use Pop\Auth\Adapter;

class AuthFileEncryptedTest extends \PHPUnit_Framework_TestCase
{

    public function testMd5()
    {
        $file = new Adapter\File(__DIR__ . '/tmp/access-encrypted.txt', Auth::ENCRYPT_MD5);
        $file->setUsername('admin_md5')
             ->setPassword('12admin34');
        $this->assertEquals(Auth::VALID, $file->authenticate());
    }

    public function testFail()
    {
        $file = new Adapter\File(__DIR__ . '/tmp/access-encrypted.txt', Auth::ENCRYPT_MD5, ['secret' => 'ssshhh']);
        $file->setUsername('admin_md5')
            ->setPassword('12admin34');
        $this->assertEquals(Auth::NOT_VALID, $file->authenticate());
    }

    public function testSha1()
    {
        $file = new Adapter\File(__DIR__ . '/tmp/access-encrypted.txt', Auth::ENCRYPT_SHA1);
        $file->setUsername('admin_sha1')
            ->setPassword('12admin34');
        $this->assertEquals(Auth::VALID, $file->authenticate());
    }

    public function testCrypt()
    {
        $file = new Adapter\File(__DIR__ . '/tmp/access-encrypted.txt', Auth::ENCRYPT_CRYPT, ['salt' => 'mysalt']);
        $file->setUsername('admin_crypt')
             ->setPassword('12admin34');
        $this->assertEquals(Auth::VALID, $file->authenticate());
        $this->assertEquals(Auth::ENCRYPT_CRYPT, $file->getEncryption());
        $this->assertTrue(isset($file->getEncryptionOptions()['salt']));
        $this->assertEquals('mysalt', $file->getEncryptionOptions()['salt']);
    }

    public function testBcrypt()
    {
        $file = new Adapter\File(__DIR__ . '/tmp/access-encrypted.txt', Auth::ENCRYPT_BCRYPT, [
            'cost'   => '08',
            'prefix' => '$2y$'
        ]);
        $file->setUsername('admin_bcrypt')
             ->setPassword('12admin34');
        $this->assertEquals(Auth::VALID, $file->authenticate());
    }

    public function testMcrypt()
    {
        $file = new Adapter\File(__DIR__ . '/tmp/access-encrypted.txt', Auth::ENCRYPT_MCRYPT, [
            'cipher' => MCRYPT_RIJNDAEL_256,
            'mode'   => MCRYPT_MODE_CBC,
            'source' => MCRYPT_RAND
        ]);
        $file->setUsername('admin_mcrypt')
             ->setPassword('12admin34');
        $this->assertEquals(Auth::VALID, $file->authenticate());
    }

    public function testCryptMd5()
    {
        $file = new Adapter\File(__DIR__ . '/tmp/access-encrypted.txt', Auth::ENCRYPT_CRYPT_MD5);
        $file->setUsername('admin_crypt_md5')
             ->setPassword('12admin34');
        $this->assertEquals(Auth::VALID, $file->authenticate());
    }

    public function testCryptSha256()
    {
        $file = new Adapter\File(__DIR__ . '/tmp/access-encrypted.txt', Auth::ENCRYPT_CRYPT_SHA_256, ['rounds' => 5000]);
        $file->setUsername('admin_crypt_sha_256')
             ->setPassword('12admin34');
        $this->assertEquals(Auth::VALID, $file->authenticate());
    }

    public function testCryptSha512()
    {
        $file = new Adapter\File(__DIR__ . '/tmp/access-encrypted.txt', Auth::ENCRYPT_CRYPT_SHA_512, ['rounds' => 5000]);
        $file->setUsername('admin_crypt_sha_512')
             ->setPassword('12admin34');
        $this->assertEquals(Auth::VALID, $file->authenticate());
    }

}