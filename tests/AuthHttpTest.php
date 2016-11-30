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

}