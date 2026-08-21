<?php

namespace Pop\Auth\Test;

use Pop\Auth\Jwt;
use Pop\Auth\Exception;
use PHPUnit\Framework\TestCase;

class AuthJwtTest extends TestCase
{

    protected function buildHmacToken(array $header, array $payload, string $secret): string
    {
        $headerB64    = $this->base64UrlEncode(json_encode($header));
        $payloadB64   = $this->base64UrlEncode(json_encode($payload));
        $signingInput = "$headerB64.$payloadB64";
        $signature    = hash_hmac('sha256', $signingInput, $secret, true);

        return $signingInput . '.' . $this->base64UrlEncode($signature);
    }

    protected function buildAsymmetricToken(array $header, array $payload, string $privateKey, bool $ec = false): string
    {
        $headerB64    = $this->base64UrlEncode(json_encode($header));
        $payloadB64   = $this->base64UrlEncode(json_encode($payload));
        $signingInput = "$headerB64.$payloadB64";

        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if ($ec) {
            $signature = $this->derToRawSignature($signature);
        }

        return $signingInput . '.' . $this->base64UrlEncode($signature);
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function derToRawSignature(string $der, int $keyByteLength = 32): string
    {
        $offset = 3; // 0x30 <total-len> 0x02 (INTEGER tag for r)
        $rLen   = ord($der[$offset]);
        $offset++;
        $r      = substr($der, $offset, $rLen);
        $offset += $rLen;

        $offset++; // 0x02 (INTEGER tag for s)
        $sLen   = ord($der[$offset]);
        $offset++;
        $s      = substr($der, $offset, $sLen);

        return str_pad(ltrim($r, "\x00"), $keyByteLength, "\x00", STR_PAD_LEFT)
             . str_pad(ltrim($s, "\x00"), $keyByteLength, "\x00", STR_PAD_LEFT);
    }

    protected function generateKeyPair(string $type): array
    {
        $config = ($type === 'ec')
            ? ['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]
            : ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];

        $resource = openssl_pkey_new($config);
        openssl_pkey_export($resource, $privateKey);
        $publicKey = openssl_pkey_get_details($resource)['key'];

        return [$privateKey, $publicKey];
    }

    public function testConstructorThrowsForUnsupportedAlgorithm()
    {
        $this->expectException(Exception::class);
        new Jwt('none', 'secret');
    }

    public function testHs256ValidTokenAuthenticates()
    {
        $secret = 'my-shared-secret';
        $token  = $this->buildHmacToken(['alg' => 'HS256', 'typ' => 'JWT'], ['sub' => 'admin'], $secret);

        $auth = new Jwt('HS256', $secret);
        $this->assertEquals(Jwt::VALID, $auth->authenticate($token));
        $this->assertTrue($auth->isAuthenticated());
        $this->assertEquals(['sub' => 'admin'], $auth->getUser());
        $this->assertFalse($auth->needsRehash());
    }

    public function testHs256WrongSecretFails()
    {
        $token = $this->buildHmacToken(['alg' => 'HS256', 'typ' => 'JWT'], ['sub' => 'admin'], 'right-secret');

        $auth = new Jwt('HS256', 'wrong-secret');
        $this->assertEquals(Jwt::NOT_VALID, $auth->authenticate($token));
    }

    public function testRs256ValidTokenAuthenticates()
    {
        [$privateKey, $publicKey] = $this->generateKeyPair('rsa');
        $token = $this->buildAsymmetricToken(['alg' => 'RS256', 'typ' => 'JWT'], ['sub' => 'admin'], $privateKey);

        $auth = new Jwt('RS256', $publicKey);
        $this->assertEquals(Jwt::VALID, $auth->authenticate($token));
    }

    public function testEs256ValidTokenAuthenticates()
    {
        [$privateKey, $publicKey] = $this->generateKeyPair('ec');
        $token = $this->buildAsymmetricToken(['alg' => 'ES256', 'typ' => 'JWT'], ['sub' => 'admin'], $privateKey, true);

        $auth = new Jwt('ES256', $publicKey);
        $this->assertEquals(Jwt::VALID, $auth->authenticate($token));
    }

    public function testExpiredTokenFails()
    {
        $secret = 'my-shared-secret';
        $token  = $this->buildHmacToken(['alg' => 'HS256', 'typ' => 'JWT'], ['sub' => 'admin', 'exp' => time() - 60], $secret);

        $auth = new Jwt('HS256', $secret);
        $this->assertEquals(Jwt::NOT_VALID, $auth->authenticate($token));
    }

    public function testNotYetValidTokenFails()
    {
        $secret = 'my-shared-secret';
        $token  = $this->buildHmacToken(['alg' => 'HS256', 'typ' => 'JWT'], ['sub' => 'admin', 'nbf' => time() + 60], $secret);

        $auth = new Jwt('HS256', $secret);
        $this->assertEquals(Jwt::NOT_VALID, $auth->authenticate($token));
    }

    public function testExpiredTokenWithinLeewayPasses()
    {
        $secret = 'my-shared-secret';
        $token  = $this->buildHmacToken(['alg' => 'HS256', 'typ' => 'JWT'], ['sub' => 'admin', 'exp' => time() - 10], $secret);

        $auth = new Jwt('HS256', $secret);
        $auth->setLeeway(30);
        $this->assertEquals(Jwt::VALID, $auth->authenticate($token));
    }

    public function testWrongAudienceFails()
    {
        $secret = 'my-shared-secret';
        $token  = $this->buildHmacToken(['alg' => 'HS256', 'typ' => 'JWT'], ['sub' => 'admin', 'aud' => 'other-api'], $secret);

        $auth = new Jwt('HS256', $secret);
        $auth->setAudience('my-api');
        $this->assertEquals(Jwt::NOT_VALID, $auth->authenticate($token));
    }

    public function testCorrectAudienceAndIssuerPass()
    {
        $secret = 'my-shared-secret';
        $token  = $this->buildHmacToken(
            ['alg' => 'HS256', 'typ' => 'JWT'],
            ['sub' => 'admin', 'aud' => 'my-api', 'iss' => 'https://auth.example.com'],
            $secret
        );

        $auth = new Jwt('HS256', $secret);
        $auth->setAudience('my-api')->setIssuer('https://auth.example.com');
        $this->assertEquals(Jwt::VALID, $auth->authenticate($token));
    }

    public function testWrongIssuerFails()
    {
        $secret = 'my-shared-secret';
        $token  = $this->buildHmacToken(['alg' => 'HS256', 'typ' => 'JWT'], ['sub' => 'admin', 'iss' => 'https://evil.example.com'], $secret);

        $auth = new Jwt('HS256', $secret);
        $auth->setIssuer('https://auth.example.com');
        $this->assertEquals(Jwt::NOT_VALID, $auth->authenticate($token));
    }

    public function testTamperedPayloadFails()
    {
        $secret = 'my-shared-secret';
        $token  = $this->buildHmacToken(['alg' => 'HS256', 'typ' => 'JWT'], ['sub' => 'admin'], $secret);

        [$header, , $signature] = explode('.', $token);
        $tamperedPayload = $this->base64UrlEncode(json_encode(['sub' => 'root']));

        $auth = new Jwt('HS256', $secret);
        $this->assertEquals(Jwt::NOT_VALID, $auth->authenticate("$header.$tamperedPayload.$signature"));
    }

    public function testMalformedTokenSegmentCountFails()
    {
        $auth = new Jwt('HS256', 'secret');
        $this->assertEquals(Jwt::NOT_VALID, $auth->authenticate('not-a-jwt'));
    }

    public function testMalformedTokenBadJsonFails()
    {
        $auth          = new Jwt('HS256', 'secret');
        $garbageHeader = $this->base64UrlEncode('not-json');
        $payload       = $this->base64UrlEncode(json_encode(['sub' => 'admin']));
        $signature     = $this->base64UrlEncode('irrelevant-signature-bytes');

        $this->assertEquals(Jwt::NOT_VALID, $auth->authenticate("$garbageHeader.$payload.$signature"));
    }

    public function testMismatchedAlgHeaderFails()
    {
        $secret = 'my-shared-secret';
        $token  = $this->buildHmacToken(['alg' => 'HS256', 'typ' => 'JWT'], ['sub' => 'admin'], $secret);

        [, $publicKey] = $this->generateKeyPair('rsa');
        $auth = new Jwt('RS256', $publicKey);
        $this->assertEquals(Jwt::NOT_VALID, $auth->authenticate($token));
    }

    public function testMalformedKeyThrows()
    {
        $token = $this->buildHmacToken(['alg' => 'RS256', 'typ' => 'JWT'], ['sub' => 'admin'], 'irrelevant');

        $this->expectException(Exception::class);
        $auth = new Jwt('RS256', 'not-a-real-key');
        $auth->authenticate($token);
    }

    public function testOversizedEs256SignatureFailsWithoutThrowing()
    {
        [, $publicKey] = $this->generateKeyPair('ec');

        $headerB64    = $this->base64UrlEncode(json_encode(['alg' => 'ES256', 'typ' => 'JWT']));
        $payloadB64   = $this->base64UrlEncode(json_encode(['sub' => 'admin']));
        $oversizedSig = $this->base64UrlEncode(str_repeat('A', 300));
        $token        = "$headerB64.$payloadB64.$oversizedSig";

        $auth = new Jwt('ES256', $publicKey);
        $this->assertEquals(Jwt::NOT_VALID, $auth->authenticate($token));
    }

}
