<?php

namespace Pop\Auth\Test;

use Pop\Auth\Http;
use PHPUnit\Framework\TestCase;

class AuthHttpTest extends TestCase
{

    public function testConstructor()
    {
        $http = new Http('http://www.google.com/', 'GET', Http::AUTH_BEARER);
        $http->setBearerToken('123456');
        $http->setRefreshToken('654321');
        $http->setRefreshTokenName('refresh_token');
        $this->assertInstanceOf('Pop\Auth\Http', $http);
        $this->assertEquals('123456', $http->getBearerToken());
        $this->assertEquals('654321', $http->getRefreshToken());
        $this->assertEquals('refresh_token', $http->getRefreshTokenName());
        $this->assertEquals(Http::VALID, $http->authenticate('username', 'password'));
        $this->assertEquals('http://www.google.com/', $http->getUri());
        $this->assertEquals('GET', $http->getMethod());
        $this->assertEquals(Http::AUTH_BEARER, $http->getType());
        $this->assertEquals(0, count($http->getScheme()));
        $this->assertEquals('1.0', $http->getResponse()->getVersion());
        $this->assertEquals('200', $http->getResponse()->getCode());
        $this->assertEquals('OK', $http->getResponse()->getMessage());
        $this->assertContains('text/html', $http->getResponse()->getHeader('Content-Type'));
        $this->assertGreaterThan(1, count($http->getResponse()->getHeaders()));
        $this->assertContains('<html', $http->getResponse()->getBody());
    }

    public function testSetAndGetRelativeUri()
    {
        $http = new Http('http://www.google.com/', 'GET');
        $http->setRelativeUri('/uri');
        $this->assertEquals('/uri', $http->getRelativeUri());
    }

    public function testSetAndGetType()
    {
        $http = new Http('http://www.google.com/', 'GET');
        $http->setType(Http::AUTH_DIGEST);
        $this->assertEquals(Http::AUTH_DIGEST, $http->getType());
    }

    public function testSetAndGetResponse()
    {
        $http = new Http('http://www.google.com/', 'GET');
        $http->setResponse(new Http\Response());
        $this->assertInstanceOf('Pop\Auth\Http\Response', $http->getResponse());
    }

    public function testInitRequest()
    {
        $http = new Http('http://www.google.com/', 'GET');
        $http->initRequest('GET');
        $this->assertNull($http->getType());
    }

    public function testResponse()
    {
        $response = new Http\Response();
        $response->setVersion('1.1');
        $response->setCode('200');
        $response->setMessage('OK');
        $response->setHeaders(['Authorization' => 'Bearer 123456']);
        $response->setBody('Hello World');
        $this->assertEquals('1.1', $response->getVersion());
        $this->assertEquals('200', $response->getCode());
        $this->assertEquals('OK', $response->getMessage());
        $this->assertEquals('Bearer 123456', $response->getHeader('Authorization'));
        $this->assertEquals('Hello World', $response->getBody());
    }

    public function testBasicHeader()
    {
        $http = new Http('http://www.google.com/', 'GET');
        $http->setUsername('username');
        $http->setPassword('password');
        $this->assertContains('Authorization: Basic ', Http\AuthHeader::createBasic($http));
    }

    public function testDataHeader()
    {
        $http = new Http('http://www.google.com/', 'GET');
        $http->setUsername('username');
        $http->setPassword('password');
        $dataHeader = Http\AuthHeader::createData($http);
        $this->assertTrue(isset($dataHeader['header']));
        $this->assertTrue(isset($dataHeader['data']));
    }

    public function testRefreshHeader()
    {
        $http = new Http('http://www.google.com/', 'GET');
        $http->setRefreshToken('123465');
        $dataHeaderXml  = Http\AuthHeader::createRefresh($http, ['Content-Type' => 'application/xml']);
        $dataHeaderJson = Http\AuthHeader::createRefresh($http, ['Content-Type' => 'application/json']);
        $this->assertTrue(isset($dataHeaderXml['header']));
        $this->assertTrue(isset($dataHeaderXml['data']));
        $this->assertTrue(isset($dataHeaderJson['header']));
        $this->assertTrue(isset($dataHeaderJson['data']));
    }

    public function testConstructorBadUri()
    {
        $this->expectException('Pop\Auth\Exception');
        $http = new Http('localhost');
    }

    public function testDecodeBody()
    {
        $encodedBody = gzencode('Test body');
        $body = Http\Response::decodeBody($encodedBody);
        $this->assertEquals('Test body', $body);

        $encodedBody = gzdeflate('Test body');
        $body = Http\Response::decodeBody($encodedBody, 'deflate');
        $this->assertEquals('Test body', $body);

        $encodedBody = 'Test body';
        $body = Http\Response::decodeBody($encodedBody, 'unknown');
        $this->assertEquals('Test body', $body);
    }

    public function testParseScheme()
    {
        $http   = new Http('http://www.google.com/', 'GET');
        $http->parseScheme('Basic realm="myRealm"');
        $this->assertEquals('myRealm', $http->getScheme()['realm']);
    }

}