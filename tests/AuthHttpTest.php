<?php

namespace Pop\Auth\Test;

use Pop\Auth\Auth;
use Pop\Auth\Adapter;

class AuthHttpTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $http = new Adapter\Http('http://www.google.com/', 'GET');
        $this->assertInstanceOf('Pop\Auth\Adapter\Http', $http);
        $this->assertEquals(Auth::VALID, $http->authenticate());
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
        $this->setExpectedException('Pop\Auth\Adapter\Exception');
        $http = new Adapter\Http('localhost');
    }

}