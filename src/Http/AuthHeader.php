<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth\Http;

use Pop\Auth\Http;

/**
 * Auth HTTP auth header class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.1.0
 */
class AuthHeader
{

    /**
     * Create auth digest header string
     *
     * @param  Http $auth
     * @throws Exception
     * @return string
     */
    public static function createDigest(Http $auth)
    {
        $scheme = $auth->getScheme();

        if (!isset($scheme['realm']) || !isset($scheme['nonce'])) {
            throw new Exception('Error: The realm and/or the nonce was not successfully parsed.');
        }

        $a1 = md5($auth->getUsername() . ':' . $scheme['realm'] . ':' . $auth->getPassword());
        $a2 = md5($auth->getMethod() . ':' . $auth->getRelativeUri());
        $r  = md5($a1 . ':' . $scheme['nonce'] . ':' . $a2);

        return 'Authorization: Digest username="' . $auth->getUsername() .
            '", realm="' . $scheme['realm'] . '", nonce="' . $scheme['nonce'] .
            '", uri="' . $auth->getRelativeUri() . '", response="' . $r . '"';
    }

    /**
     * Create auth basic header string
     *
     * @param  Http $auth
     * @return string
     */
    public static function createBasic(Http $auth)
    {
        return 'Authorization: Basic ' . base64_encode($auth->getUsername() . ':' . $auth->getPassword());
    }

    /**
     * Create auth bearer token header string
     *
     * @param  Http $auth
     * @return string
     */
    public static function createBearer(Http $auth)
    {
        return 'Authorization: Bearer ' . $auth->getBearerToken();
    }

    /**
     * Create auth data header string
     *
     * @param  Http  $auth
     * @return array
     */
    public static function createData(Http $auth)
    {
        $data   = http_build_query(['username' => $auth->getUsername(), 'password' => $auth->getPassword()]);
        $header = "Content-Type: application/x-www-form-urlencoded\r\n" . "Content-Length: " . strlen($data) . "\r\n";

        return [
            'header' => $header,
            'data'   => $data
        ];
    }

    /**
     * Create auth refresh header string
     *
     * @param  Http  $auth
     * @param  array $headers
     * @return array
     */
    public static function createRefresh(Http $auth, array $headers)
    {
        $header = 'Authorization: Bearer ' . $auth->getBearerToken() . "\r\n";
        if (isset($headers['Content-Type']) && (strpos($headers['Content-Type'], 'json') !== false)) {
            $data    = json_encode([$auth->getRefreshTokenName() => $auth->getRefreshToken()]);
            $header .= "Content-Length: " . strlen($data) . "\r\n";
        } else {
            $data    = http_build_query([$auth->getRefreshTokenName() => $auth->getRefreshToken()]);
            $header .= "Content-Type: application/x-www-form-urlencoded\r\n" . "Content-Length: " . strlen($data) . "\r\n";
        }

        return [
            'header' => $header,
            'data'   => $data
        ];
    }

}
