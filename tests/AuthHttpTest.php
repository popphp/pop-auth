<?php

namespace Pop\Auth\Test;

use Pop\Auth\Http;
use Pop\Http\Client;
use Pop\Http\Auth;
use PHPUnit\Framework\TestCase;

class AuthHttpTest extends TestCase
{

    public function testConstructor()
    {
        $http = new Http(new Client('http://localhost/'), Auth::createBasic('username', 'password'));
        $http->setRefreshToken('654321');
        $http->setRefreshTokenName('refresh_token');
        $this->assertInstanceOf('Pop\Auth\Http', $http);
        $this->assertInstanceOf('Pop\Http\Client', $http->getClient());
        $this->assertInstanceOf('Pop\Http\Client', $http->client());
        $this->assertEquals('654321', $http->getRefreshToken());
        $this->assertEquals('refresh_token', $http->getRefreshTokenName());
        $this->assertTrue($http->hasRefreshToken());
        $this->assertTrue($http->hasRefreshTokenName());
        $this->assertTrue($http->hasClient());
        $this->assertEquals(Http::VALID, $http->authenticate('username', 'password'));
        $this->assertNotNull($http->getResultResponse());
    }

    public function testUserPassNoAuth()
    {
        $http = new Http(new Client('http://localhost/'));
        $http->setUsername('username')
            ->setPassword('password');
        $this->assertEquals('username', $http->getUsername());
        $this->assertEquals('password', $http->getPassword());
    }

}