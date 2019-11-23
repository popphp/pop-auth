<?php

namespace Pop\Auth\Test;

use Pop\Auth\Http;
use PHPUnit\Framework\TestCase;

class AuthHttpTest extends TestCase
{

    public function testConstructor()
    {
        $http = new Http('http://localhost/', Http::AUTH_BEARER, 'POST');
        $http->setContentType('application/json');
        $http->setBearerToken('123456');
        $http->setRefreshToken('654321');
        $http->setRefreshTokenName('refresh_token');
        $this->assertInstanceOf('Pop\Auth\Http', $http);
        $this->assertInstanceOf('Pop\Http\Client\Stream', $http->getStream());
        $this->assertInstanceOf('Pop\Http\Client\Stream', $http->stream());
        $this->assertEquals('application/json', $http->getContentType());
        $this->assertEquals('123456', $http->getBearerToken());
        $this->assertEquals('654321', $http->getRefreshToken());
        $this->assertEquals('refresh_token', $http->getRefreshTokenName());
        $this->assertEquals(Http::AUTH_BEARER, $http->getType());
        $this->assertEquals(0, count($http->getScheme()));
        $this->assertTrue($http->hasStream());
        $this->assertTrue($http->hasContentType());
        $this->assertTrue($http->hasBearerToken());
        $this->assertTrue($http->hasRefreshToken());
        $this->assertTrue($http->hasRefreshTokenName());
        $this->assertTrue($http->hasType());
        $this->assertFalse($http->hasScheme());
        $this->assertEquals(Http::VALID, $http->authenticate('username', 'password'));
        $this->assertNotNull($http->getResultResponse());
    }

    public function testSetAndGetType()
    {
        $http = new Http('http://localhost/', null, 'POST');
        $http->setType(Http::AUTH_DIGEST);
        $this->assertEquals(Http::AUTH_DIGEST, $http->getType());
    }

    public function testInitRequest()
    {
        $http = new Http('http://localhost/', null, 'POST');
        $http->initRequest();
        $this->assertNull($http->getType());
    }

    public function testInitRequestException()
    {
        $this->expectException('Pop\Auth\Exception');
        $http = new Http();
        $http->initRequest();
    }

    public function testParseScheme()
    {
        $http   = new Http('http://localhost/', null, 'POST');
        $http->parseScheme('Basic realm="myRealm"');
        $this->assertEquals('myRealm', $http->getScheme()['realm']);
    }

    public function testBasic()
    {
        $http = new Http('http://localhost/', Http::AUTH_BASIC, 'POST');
        $http->validate();
        $this->assertTrue($http->stream()->request()->hasHeader('Authorization'));
        $this->assertContains('Basic', $http->stream()->request()->getHeader('Authorization')->getValue());
    }

    public function testBearer()
    {
        $http = new Http('http://localhost/', Http::AUTH_BEARER, 'POST');
        $http->validate();
        $this->assertTrue($http->stream()->request()->hasHeader('Authorization'));
        $this->assertContains('Bearer', $http->stream()->request()->getHeader('Authorization')->getValue());
    }

    public function testUrlData()
    {
        $http = new Http('http://localhost/', Http::AUTH_URL_DATA, 'POST');
        $http->setUsername('admin')
            ->setPassword('password');
        $http->validate();
        $this->assertTrue($http->stream()->request()->hasHeader('Content-Type'));
        $this->assertEquals('application/x-www-form-urlencoded', $http->stream()->request()->getHeader('Content-Type')->getValue());
    }

    public function testFormData()
    {
        $http = new Http('http://localhost/', Http::AUTH_FORM_DATA, 'POST');
        $http->setUsername('admin')
            ->setPassword('password');
        $http->validate();
        $this->assertTrue($http->stream()->request()->hasHeader('Content-Type'));
        $this->assertEquals('multipart/form-data', $http->stream()->request()->getHeader('Content-Type')->getValue());
    }

    public function testRefreshAsJson()
    {
        $http = new Http('http://localhost/', Http::AUTH_REFRESH, 'POST');
        $http->setContentType('application/json');
        $http->setRefreshToken('123456789');
        $http->validate();
        $this->assertTrue($http->stream()->request()->hasHeader('Authorization'));
        $this->assertContains('Bearer', $http->stream()->request()->getHeader('Authorization')->getValue());
    }

    public function testRefreshAsUrlForm()
    {
        $http = new Http('http://localhost/', Http::AUTH_REFRESH, 'POST');
        $http->setContentTypeAsUrlForm();
        $http->setRefreshToken('123456789');
        $http->validate();
        $this->assertTrue($http->stream()->request()->hasHeader('Authorization'));
        $this->assertContains('Bearer', $http->stream()->request()->getHeader('Authorization')->getValue());
    }

    public function testRefreshAsMultipartForm()
    {
        $http = new Http('http://localhost/', Http::AUTH_REFRESH, 'POST');
        $http->setContentTypeAsMultipartForm();
        $http->setRefreshToken('123456789');
        $http->validate();
        $this->assertTrue($http->stream()->request()->hasHeader('Authorization'));
        $this->assertContains('Bearer', $http->stream()->request()->getHeader('Authorization')->getValue());
    }

    public function testDigest()
    {
        $http = new Http('http://localhost/', Http::AUTH_DIGEST, 'POST');
        $http->parseScheme('Basic realm="myRealm, nonce=123456789"');
        $http->validate();
        $this->assertTrue($http->stream()->request()->hasHeader('Authorization'));
        $this->assertContains('Digest', $http->stream()->request()->getHeader('Authorization')->getValue());
    }

    public function testDigestException()
    {
        $this->expectException('Pop\Auth\Exception');
        $http = new Http('http://localhost/', Http::AUTH_DIGEST, 'POST');
        $http->validate();
    }

}