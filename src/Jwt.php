<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth;

use Pop\Crypt\Signature\Verifier;

/**
 * Jwt auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Jwt extends AbstractAuth
{

    use AdapterUserTrait;

    /**
     * Supported algorithms
     * @var array
     */
    const ALGORITHMS = ['HS256', 'RS256', 'ES256'];

    /**
     * Expected byte length of a raw ES256 (P-256) JOSE signature: 32-byte R + 32-byte S (RFC 7518 §3.4)
     * @var int
     */
    const ES256_SIGNATURE_LENGTH = 64;

    /**
     * Algorithm
     * @var string
     */
    protected string $algorithm;

    /**
     * Key (shared secret for HS256, PEM public key for RS256/ES256)
     * @var string
     */
    protected string $key;

    /**
     * Audience to validate the 'aud' claim against
     * @var ?string
     */
    protected ?string $audience = null;

    /**
     * Issuer to validate the 'iss' claim against
     * @var ?string
     */
    protected ?string $issuer = null;

    /**
     * Leeway (in seconds) allowed for exp/nbf claim comparisons
     * @var int
     */
    protected int $leeway = 0;

    /**
     * Constructor
     *
     * Instantiate the Jwt auth adapter object
     *
     * @param  string $algorithm
     * @param  string $key
     * @throws Exception
     */
    public function __construct(string $algorithm, string $key)
    {
        if (!in_array($algorithm, self::ALGORITHMS, true)) {
            throw new Exception("The algorithm '" . $algorithm . "' is not supported.");
        }

        $this->algorithm = $algorithm;
        $this->key        = $key;
    }

    /**
     * Set the audience to validate the 'aud' claim against
     *
     * @param  string $audience
     * @return Jwt
     */
    public function setAudience(string $audience): Jwt
    {
        $this->audience = $audience;
        return $this;
    }

    /**
     * Set the issuer to validate the 'iss' claim against
     *
     * @param  string $issuer
     * @return Jwt
     */
    public function setIssuer(string $issuer): Jwt
    {
        $this->issuer = $issuer;
        return $this;
    }

    /**
     * Set the leeway (in seconds) allowed for exp/nbf claim comparisons
     *
     * @param  int $seconds
     * @return Jwt
     */
    public function setLeeway(int $seconds): Jwt
    {
        $this->leeway = $seconds;
        return $this;
    }

    /**
     * Method to authenticate
     *
     * @param  string  $token
     * @param  ?string $secondary
     * @throws Exception
     * @return int
     */
    public function authenticate(string $token, ?string $secondary = null): int
    {
        $this->result      = self::NOT_VALID;
        $this->needsRehash = false;
        $this->user        = null;

        $segments = explode('.', $token);
        if (count($segments) !== 3) {
            return $this->result;
        }

        [$headerB64, $payloadB64, $signatureB64] = $segments;

        $header  = json_decode(self::base64UrlDecode($headerB64), true);
        $payload = json_decode(self::base64UrlDecode($payloadB64), true);

        if (!is_array($header) || !is_array($payload) || (($header['alg'] ?? null) !== $this->algorithm)) {
            return $this->result;
        }

        $signature    = self::base64UrlDecode($signatureB64);
        $signingInput = $headerB64 . '.' . $payloadB64;

        try {
            $verified = match ($this->algorithm) {
                'HS256' => Verifier::hmac($signingInput, $signature, $this->key),
                'RS256' => Verifier::rsa($signingInput, $signature, $this->key),
                'ES256' => (strlen($signature) === self::ES256_SIGNATURE_LENGTH)
                    && Verifier::ec($signingInput, self::esSignatureToDer($signature), $this->key),
                default => throw new Exception('Unsupported algorithm.'),
            };
        } catch (\Throwable $e) {
            throw new Exception('Unable to verify the token signature: ' . $e->getMessage(), 0, $e);
        }

        if (!$verified || !$this->claimsAreValid($payload)) {
            return $this->result;
        }

        $this->user   = $payload;
        $this->result = self::VALID;

        return $this->result;
    }

    /**
     * Determine if the token's claims (exp/nbf/aud/iss) are valid
     *
     * @param  array $payload
     * @return bool
     */
    protected function claimsAreValid(array $payload): bool
    {
        $now = time();

        if (isset($payload['exp']) && ($now > ((int)$payload['exp'] + $this->leeway))) {
            return false;
        }

        if (isset($payload['nbf']) && ($now < ((int)$payload['nbf'] - $this->leeway))) {
            return false;
        }

        if ($this->audience !== null) {
            $aud = $payload['aud'] ?? null;
            if (!in_array($this->audience, is_array($aud) ? $aud : [$aud], true)) {
                return false;
            }
        }

        if (($this->issuer !== null) && (($payload['iss'] ?? null) !== $this->issuer)) {
            return false;
        }

        return true;
    }

    /**
     * Base64url-decode a JWT segment
     *
     * @param  string $data
     * @return string
     */
    protected static function base64UrlDecode(string $data): string
    {
        $padded  = str_pad($data, strlen($data) + ((4 - (strlen($data) % 4)) % 4), '=');
        $decoded = base64_decode(strtr($padded, '-_', '+/'), true);
        return ($decoded === false) ? '' : $decoded;
    }

    /**
     * Convert a JOSE ES256 raw R||S signature into the DER-encoded ASN.1 sequence openssl_verify() expects
     *
     * @param  string $signature
     * @return string
     */
    protected static function esSignatureToDer(string $signature): string
    {
        $length = (int)(strlen($signature) / 2);
        $r      = ltrim(substr($signature, 0, $length), "\x00");
        $s      = ltrim(substr($signature, $length), "\x00");

        if (($r === '') || ((ord($r[0]) & 0x80) !== 0)) {
            $r = "\x00" . $r;
        }
        if (($s === '') || ((ord($s[0]) & 0x80) !== 0)) {
            $s = "\x00" . $s;
        }

        $sequence = self::derInteger($r) . self::derInteger($s);

        return "\x30" . self::derLength(strlen($sequence)) . $sequence;
    }

    /**
     * DER-encode a single ASN.1 INTEGER
     *
     * @param  string $bytes
     * @return string
     */
    protected static function derInteger(string $bytes): string
    {
        return "\x02" . self::derLength(strlen($bytes)) . $bytes;
    }

    /**
     * DER-encode a length value
     *
     * @param  int $length
     * @return string
     */
    protected static function derLength(int $length): string
    {
        return ($length < 128) ? chr($length) : chr(0x81) . chr($length);
    }

}
