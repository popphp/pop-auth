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
namespace Pop\Auth;

/**
 * Http auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Http extends AbstractAuth
{

    /**
     * HTTP auth constants
     */
    const AUTH_BASIC   = 'Basic';
    const AUTH_DIGEST  = 'Digest';
    const AUTH_BEARER  = 'Bearer';
    const AUTH_REFRESH = 'Refresh';
    const AUTH_DATA    = 'Data';

    /**
     * Auth URI
     * @var string
     */
    protected $uri = null;

    /**
     * Auth bearer token
     * @var string
     */
    protected $bearerToken = null;

    /**
     * Auth refresh token
     * @var string
     */
    protected $refreshToken = null;

    /**
     * Auth refresh token name
     * @var string
     */
    protected $refreshTokenName = 'refresh';

    /**
     * Auth relative URI
     * @var string
     */
    protected $relativeUri = null;

    /**
     * Auth method
     * @var string
     */
    protected $method = 'GET';

    /**
     * Auth type
     * @var string
     */
    protected $type = null;

    /**
     * Scheme values
     * @var array
     */
    protected $scheme = [];

    /**
     * HTTP version
     * @var string
     */
    protected $version = '1.1';

    /**
     * Response code
     * @var int
     */
    protected $code = null;

    /**
     * Response message
     * @var string
     */
    protected $message = null;

    /**
     * Response headers
     * @var array
     */
    protected $headers = [];

    /**
     * Response body
     * @var string
     */
    protected $body = null;

    /**
     * Constructor
     *
     * Instantiate the Http auth adapter object
     *
     * @param string $uri
     * @param string $method
     * @param string $type
     */
    public function __construct($uri, $method = 'POST', $type = null)
    {
        $this->setUri($uri);

        $this->relativeUri = substr($uri, (strpos($uri, '://') + 3));
        $this->relativeUri = substr($this->relativeUri, strpos($this->relativeUri, '/'));

        $method = strtoupper($method);

        if (($method == 'GET') || ($method == 'POST') || ($method == 'PUT') || ($method == 'PATCH')) {
            $this->method = $method;
        }

        if (null !== $type) {
            $this->type = $type;
        }
    }

    /**
     * Set the URI
     *
     * @param  string $uri
     * @throws Exception
     * @return Http
     */
    public function setUri($uri)
    {
        if (substr($uri, 0, 4) != 'http') {
            throw new Exception('Error: The URI parameter must be a full URI with the HTTP scheme.');
        }

        $this->uri = $uri;
        return $this;
    }

    /**
     * Set the relative URI
     *
     * @param  string $relativeUri
     * @return Http
     */
    public function setRelativeUri($relativeUri)
    {
        $this->relativeUri = $relativeUri;
        return $this;
    }

    /**
     * Set the bearer token
     *
     * @param  string $bearerToken
     * @return Http
     */
    public function setBearerToken($bearerToken)
    {
        $this->bearerToken = $bearerToken;
        return $this;
    }

    /**
     * Set the refresh token
     *
     * @param  string $refreshToken
     * @return Http
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * Set the refresh token name
     *
     * @param  string $refreshTokenName
     * @return Http
     */
    public function setRefreshTokenName($refreshTokenName)
    {
        $this->refreshTokenName = $refreshTokenName;
        return $this;
    }

    /**
     * Get the URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Get the relative URI
     *
     * @return string
     */
    public function getRelativeUri()
    {
        return $this->relativeUri;
    }

    /**
     * Get the bearer token
     *
     * @return string
     */
    public function getBearerToken()
    {
        return $this->bearerToken;
    }

    /**
     * Get the refresh token
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Get the refresh token name
     *
     * @return string
     */
    public function getRefreshTokenName()
    {
        return $this->refreshTokenName;
    }

    /**
     * Get the auth type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get the auth scheme
     *
     * @return array
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * Get the HTTP version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get the HTTP code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Get the HTTP message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Get the HTTP response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get an HTTP response header
     *
     * @param  string $name
     * @return mixed
     */
    public function getHeader($name)
    {
        return (isset($this->headers[$name])) ? $this->headers[$name] : null;
    }

    /**
     * Get the HTTP response body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Method to authenticate
     *
     * @param  string $username
     * @param  string $password
     * @param  array  $headers
     * @param  array  $contextOptions
     * @param  array  $contextParams
     * @return int
     */
    public function authenticate($username, $password, array $headers = null, array $contextOptions = [], array $contextParams = null)
    {
        $this->setUsername($username);
        $this->setPassword($password);

        if (null === $this->type) {
            $this->generateRequest();
        }

        return $this->validate($headers, $contextOptions, $contextParams);
    }

    /**
     * Method to validate authentication
     *
     * @param  array $headers
     * @param  array $contextOptions
     * @param  array $contextParams
     * @return int
     */
    public function validate(array $headers = null, array $contextOptions = [], array $contextParams = null)
    {
        $context = [
            'http' => [
                'method' => $this->method,
                'header' => null
            ]
        ];

        $context = array_merge_recursive($context, $contextOptions);

        if (null !== $headers) {
            foreach ($headers as $header => $value) {
                $context['http']['header'] .= $header . ": " . $value . "\r\n";
            }
        }

        switch ($this->type) {
            case self::AUTH_DIGEST:
                $a1 = md5($this->username . ':' . $this->scheme['realm'] . ':' . $this->password);
                $a2 = md5($this->method . ':' . $this->relativeUri);
                $r  = md5($a1 . ':' . $this->scheme['nonce'] . ':' . $a2);
                $context['http']['header'] .= 'Authorization: Digest username="' . $this->username .
                    '", realm="' . $this->scheme['realm'] . '", nonce="' . $this->scheme['nonce'] .
                    '", uri="' . $this->relativeUri . '", response="' . $r . '"';
                break;

            case self::AUTH_BASIC:
                $context['http']['header'] .= 'Authorization: Basic ' . base64_encode($this->username . ':' . $this->password);
                break;

            case self::AUTH_BEARER:
                $context['http']['header'] .= 'Authorization: Bearer ' . $this->bearerToken;
                break;

            case self::AUTH_REFRESH:
                $context['http']['header'] .= 'Authorization: Bearer ' . $this->bearerToken . "\r\n";
                if (isset($headers['Content-Type']) && (strpos($headers['Content-Type'], 'json') !== false)) {
                    $data = json_encode([$this->refreshTokenName => $this->refreshToken]);
                    $context['http']['header'] .= "Content-Length: " . strlen($data) . "\r\n";
                    $context['http']['content'] = $data;
                } else {
                    $data = http_build_query([$this->refreshTokenName => $this->refreshToken]);
                    $context['http']['header'] .= "Content-Type: application/x-www-form-urlencoded\r\n"
                        . "Content-Length: " . strlen($data) . "\r\n";
                    $context['http']['content'] = $data;
                }

                break;

            // GET is not allowed for security reasons
            case self::AUTH_DATA:
                $data = http_build_query(['username' => $this->username, 'password' => $this->password]);
                $context['http']['header'] .= "Content-Type: application/x-www-form-urlencoded\r\n"
                    . "Content-Length: " . strlen($data) . "\r\n";
                $context['http']['content'] = $data;
                break;
        }

        $this->sendRequest($context, $contextParams);
        $this->result = (int)($this->code == 200);
        return $this->result;
    }

    /**
     * Generate the request
     *
     * @return void
     */
    public function generateRequest()
    {
        $context = [
            'http' => [
                'method' => $this->method
            ]
        ];
        $this->sendRequest($context);

        // Check for the WWW Auth header and parse it
        if (isset($this->headers['WWW-Authenticate'])) {
            $this->parseScheme($this->headers['WWW-Authenticate']);
        } else if (isset($this->headers['WWW-authenticate'])) {
            $this->parseScheme($this->headers['WWW-authenticate']);
        } else if (isset($this->headers['Www-Authenticate'])) {
            $this->parseScheme($this->headers['Www-Authenticate']);
        } else if (isset($this->headers['Www-Authenticate'])) {
            $this->parseScheme($this->headers['www-authenticate']);
        }
    }

    /**
     * Parse the scheme
     *
     * @param  string $wwwAuth
     * @return void
     */
    public function parseScheme($wwwAuth)
    {
        if (strpos($wwwAuth, ' ') !== false) {
            $this->type = substr($wwwAuth, 0, strpos($wwwAuth, ' '));
            $scheme     = explode(', ', substr($wwwAuth, (strpos($wwwAuth, ' ') + 1)));

            foreach ($scheme as $sch) {
                $sch   = trim($sch);
                $name  = substr($sch,0, strpos($sch, '='));
                $value = substr($sch, (strpos($sch, '=') + 1));
                if ((substr($value, 0, 1) == '"') && (substr($value, -1) == '"')) {
                    $value = substr($value, 1);
                    $value = substr($value, 0, -1);
                }
                $this->scheme[$name] = $value;
            }
        } else {
            $this->type = $wwwAuth;
        }
    }

    /**
     * Send the request
     *
     * @param  array $context
     * @param  array $contextParams
     * @return void
     */
    protected function sendRequest(array $context = null, array $contextParams = null)
    {
        $http_response_header = null;
        $firstLine            = null;

        if (null !== $context) {
            $stream = (null !== $contextParams) ?
                @fopen($this->uri, 'r', false, stream_context_create($context, $contextParams)) :
                @fopen($this->uri, 'r', false, stream_context_create($context));
        } else {
            $stream = @fopen($this->uri, 'r');
        }

        if ($stream != false) {
            $meta = stream_get_meta_data($stream);
            $firstLine = $meta['wrapper_data'][0];
            unset($meta['wrapper_data'][0]);
            $allHeadersAry = $meta['wrapper_data'];
            $this->body = stream_get_contents($stream);
        } else if (null !== $http_response_header) {
            $firstLine = $http_response_header[0];
            unset($http_response_header[0]);
            $allHeadersAry = $http_response_header;
            $this->body = null;
        }

        if (null !== $firstLine) {
            // Get the version, code and message
            $this->version = substr($firstLine, 0, strpos($firstLine, ' '));
            $this->version = substr($this->version, (strpos($this->version, '/') + 1));
            preg_match('/\d\d\d/', trim($firstLine), $match);
            $this->code    = $match[0];
            $this->message = str_replace('HTTP/' . $this->version . ' ' . $this->code . ' ', '', $firstLine);

            // Get the headers
            foreach ($allHeadersAry as $hdr) {
                $name = substr($hdr, 0, strpos($hdr, ':'));
                $value = substr($hdr, (strpos($hdr, ' ') + 1));
                $this->headers[trim($name)] = trim($value);
            }

            // If the body content is encoded, decode the body content
            if (array_key_exists('Content-Encoding', $this->headers)) {
                if (isset($this->headers['Transfer-Encoding']) && ($this->headers['Transfer-Encoding'] == 'chunked')) {
                    $this->body = self::decodeChunkedBody($this->body);
                }
                $this->body = self::decodeBody($this->body, $this->headers['Content-Encoding']);
            }
        }
    }

    /**
     * Decode the body data.
     *
     * @param  string $body
     * @param  string $decode
     * @throws Exception
     * @return string
     */
    public static function decodeBody($body, $decode = 'gzip')
    {
        switch ($decode) {
            // GZIP compression
            case 'gzip':
                if (!function_exists('gzinflate')) {
                    throw new Exception('Gzip compression is not available.');
                }
                $decodedBody = gzinflate(substr($body, 10));
                break;

            // Deflate compression
            case 'deflate':
                if (!function_exists('gzinflate')) {
                    throw new Exception('Deflate compression is not available.');
                }
                $zlibHeader = unpack('n', substr($body, 0, 2));
                $decodedBody = ($zlibHeader[1] % 31 == 0) ? gzuncompress($body) : gzinflate($body);
                break;

            // Unknown compression
            default:
                $decodedBody = $body;

        }

        return $decodedBody;
    }

    /**
     * Decode a chunked transfer-encoded body and return the decoded text
     *
     * @param string $body
     * @return string
     */
    public static function decodeChunkedBody($body)
    {
        $decoded = '';

        while($body != '') {
            $lfPos = strpos($body, "\012");

            if ($lfPos === false) {
                $decoded .= $body;
                break;
            }

            $chunkHex = trim(substr($body, 0, $lfPos));
            $scPos    = strpos($chunkHex, ';');

            if ($scPos !== false) {
                $chunkHex = substr($chunkHex, 0, $scPos);
            }

            if ($chunkHex == '') {
                $decoded .= substr($body, 0, $lfPos);
                $body = substr($body, $lfPos + 1);
                continue;
            }

            $chunkLength = hexdec($chunkHex);

            if ($chunkLength) {
                $decoded .= substr($body, $lfPos + 1, $chunkLength);
                $body = substr($body, $lfPos + 2 + $chunkLength);
            } else {
                $body = '';
            }
        }

        return $decoded;
    }

}