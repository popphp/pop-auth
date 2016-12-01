<?php

namespace Pop\Auth\Test;

use Pop\Auth\Http;

class AuthHttpTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $http = new Http('http://www.google.com/', 'GET');
        $this->assertInstanceOf('Pop\Auth\Http', $http);
        $this->assertEquals(Http::VALID, $http->authenticate('username', 'password'));
        $this->assertNull($http->getType());
        $this->assertEquals(0, count($http->getScheme()));
        $this->assertEquals('1.0', $http->getVersion());
        $this->assertEquals('200', $http->getCode());
        $this->assertEquals('OK', $http->getMessage());
        $this->assertContains('text/html', $http->getHeader('Content-Type'));
        $this->assertGreaterThan(1, count($http->getHeaders()));
        $this->assertContains('<html', $http->getBody());
    }

    public function testConstructorBadUri()
    {
        $this->expectException('Pop\Auth\Exception');
        $http = new Http('localhost');
    }

    public function testDecodeBody()
    {
        $encodedBody = gzencode('Test body');
        $body = Http::decodeBody($encodedBody);
        $this->assertEquals('Test body', $body);

        $encodedBody = gzdeflate('Test body');
        $body = Http::decodeBody($encodedBody, 'deflate');
        $this->assertEquals('Test body', $body);

        $encodedBody = 'Test body';
        $body = Http::decodeBody($encodedBody, 'unknown');
        $this->assertEquals('Test body', $body);
    }

    public function testParseScheme()
    {
        $http   = new Http('http://www.google.com/', 'GET');
        $http->parseScheme('Basic realm="myRealm"');
        $this->assertEquals('myRealm', $http->getScheme()['realm']);
    }

}