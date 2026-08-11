<?php

namespace Pop\Auth\Test;

use Pop\Auth\Http;
use Pop\Auth\Exception;
use Pop\Http\Client;
use Pop\Http\Auth;
use PHPUnit\Framework\TestCase;

class AuthHttpTest extends TestCase
{

    public function testConstructor()
    {
        $http = new Http(new Client('http://localhost/'), Auth::createBasic('username', 'password'));
        $this->assertInstanceOf('Pop\Auth\Http', $http);
        $this->assertInstanceOf('Pop\Http\Client', $http->getClient());
        $this->assertInstanceOf('Pop\Http\Client', $http->client());
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

        // With no Pop\Http\Auth attached to the client, credentials must be routed into
        // the request's POST data under the configured field names, not silently dropped.
        $this->assertTrue($http->getClient()->hasData('username'));
        $this->assertEquals('username', $http->getClient()->getData('username'));
        $this->assertTrue($http->getClient()->hasData('password'));
        $this->assertEquals('password', $http->getClient()->getData('password'));
    }

    public function testAuthenticateThrowsWithNoClient()
    {
        $this->expectException(Exception::class);
        $http = new Http();
        $http->authenticate('username', 'password');
    }

    public function testAuthenticateReturnsNotValidForNon200Response()
    {
        $http = new Http(new Client('http://localhost/this-path-should-not-exist-404'));
        $this->assertEquals(Http::NOT_VALID, $http->authenticate('username', 'password'));
        $this->assertFalse($http->isAuthenticated());
    }

    public function testGetUserFromParsedJsonResponse()
    {
        $port = 18099;
        $process = proc_open(
            [PHP_BINARY, '-S', '127.0.0.1:' . $port, '-t', __DIR__ . '/TestAsset'],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        $this->assertNotFalse($process, 'Failed to start the built-in PHP server for this test.');
        $this->waitForServer('127.0.0.1', $port);

        try {
            $http = new Http(new Client('http://127.0.0.1:' . $port . '/http-fixture.php', ['method' => 'post']));
            $this->assertEquals(Http::VALID, $http->authenticate('admin', 'whatever'));
            $this->assertEquals(['username' => 'admin', 'email' => 'admin@example.com'], $http->getUser());
        } finally {
            proc_terminate($process);
            proc_close($process);
        }
    }

    private function waitForServer(string $host, int $port, int $maxAttempts = 20): void
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $connection = @fsockopen($host, $port, $errno, $errstr, 0.1);
            if ($connection !== false) {
                fclose($connection);
                return;
            }
            usleep(50000);
        }
        $this->fail('PHP built-in server did not start listening in time.');
    }

}