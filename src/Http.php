<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth;

use Pop\Http\Client;
/**
 * Http auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.0
 */
class Http extends AbstractAuth
{

    /**
     * HTTP auth type constants
     */
    const AUTH_BASIC     = 'BASIC';
    const AUTH_DIGEST    = 'DIGEST';
    const AUTH_BEARER    = 'BEARER';
    const AUTH_URL_DATA  = 'URL_DATA';
    const AUTH_FORM_DATA = 'FORM_DATA';
    const AUTH_REFRESH   = 'REFRESH';

    /**
     * Auth client stream
     * @var Client\Stream
     */
    protected $stream = null;

    /**
     * Auth content type
     * @var string
     */
    protected $contentType = null;

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
     * Constructor
     *
     * Instantiate the Http auth adapter object
     *
     * @param mixed  $stream
     * @param string $type
     * @param string $method
     */
    public function __construct($stream = null, $type = null, $method = 'POST')
    {
        if (null !== $stream) {
            if (is_string($stream)) {
                $stream = new Client\Stream($stream, $method);
            }
            $this->setStream($stream);
        }
        if (null !== $type) {
            $this->setType($type);
        }
    }

    /**
     * Set stream
     *
     * @param  Client\Stream $stream
     * @return Http
     */
    public function setStream(Client\Stream $stream)
    {
        $this->stream = $stream;
        return $this;
    }

    /**
     * Set content type
     *
     * @param  string $contentType
     * @return Http
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Set content type as URL form
     *
     * @return Http
     */
    public function setContentTypeAsUrlForm()
    {
        $this->contentType = 'application/x-www-form-urlencoded';
        return $this;
    }

    /**
     * Set content type as multipart form
     *
     * @return Http
     */
    public function setContentTypeAsMultipartForm()
    {
        $this->contentType = 'multipart/form-data';
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
     * Set type
     *
     * @param  string $type
     * @return Http
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get stream
     *
     * @return Client\Stream
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * Get stream (alias method)
     *
     * @return Client\Stream
     */
    public function stream()
    {
        return $this->stream;
    }

    /**
     * Get result response
     *
     * @return array
     */
    public function getResultResponse()
    {
        $resultResponse = null;
        if (($this->stream->hasResponse()) && ($this->stream->getResponse()->hasBody())) {
            $resultResponse = $this->stream->getResponse()->getBody()->getContent();
            if ($this->stream->getResponse()->hasHeader('Content-Type')) {
                if ($this->stream->getResponse()->getHeader('Content-Type')->getValue() == 'application/json') {
                    $resultResponse = json_decode($resultResponse, true);
                } else if ($this->stream->getResponse()->getHeader('Content-Type')->getValue() == 'application/x-www-form-urlencoded') {
                    parse_str($resultResponse, $resultResponse);
                }
            }
        }
        return $resultResponse;
    }

    /**
     * Get content type
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
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
     * Has stream
     *
     * @return boolean
     */
    public function hasStream()
    {
        return (null !== $this->stream);
    }

    /**
     * Has content type
     *
     * @return boolean
     */
    public function hasContentType()
    {
        return (null !== $this->contentType);
    }

    /**
     * Has bearer token
     *
     * @return boolean
     */
    public function hasBearerToken()
    {
        return (null !== $this->bearerToken);
    }

    /**
     * Has refresh token
     *
     * @return boolean
     */
    public function hasRefreshToken()
    {
        return (null !== $this->refreshToken);
    }

    /**
     * Has refresh token name
     *
     * @return boolean
     */
    public function hasRefreshTokenName()
    {
        return (null !== $this->refreshTokenName);
    }

    /**
     * Has auth type
     *
     * @return boolean
     */
    public function hasType()
    {
        return (null !== $this->type);
    }

    /**
     * Has an auth scheme
     *
     * @return boolean
     */
    public function hasScheme()
    {
        return (!empty($this->scheme));
    }

    /**
     * Initialize the auth request
     *
     * @throws Exception
     * @return void
     */
    public function initRequest()
    {
        if (null === $this->stream) {
            throw new Exception('Error: The stream has not been set.');
        }

        $this->stream->send();

        if (($this->stream->hasResponse()) && ($this->stream->response()->hasHeaders())) {
            $wwwHeaders = ['WWW-Authenticate','WWW-authenticate','Www-Authenticate','www-authenticate'];
            foreach ($wwwHeaders as $wwwHeader) {
                if ($this->stream->response()->hasHeader($wwwHeader)) {
                    $this->type = $this->parseScheme($this->stream->response()->getHeader($wwwHeader)->getValue());
                    break;
                }
            }
        }
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
    public function authenticate($username, $password, array $headers = [], array $contextOptions = [], array $contextParams = [])
    {
        $this->setUsername($username);
        $this->setPassword($password);

        if ((null === $this->type) || (empty($this->scheme) && ($this->type == self::AUTH_DIGEST))) {
            $this->initRequest();
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
    public function validate(array $headers = [], array $contextOptions = [], array $contextParams = [])
    {
        switch ($this->type) {
            case self::AUTH_DIGEST:
                $headers['Authorization'] = $this->createDigest();
                break;
            case self::AUTH_BASIC:
                $headers['Authorization'] = 'Basic ' . base64_encode($this->username . ':' . $this->password);
                break;
            case self::AUTH_BEARER:
                $headers['Authorization'] = 'Bearer ' . $this->bearerToken;
                break;
            case self::AUTH_URL_DATA:
                $this->stream->setFields([
                    'username' => $this->username,
                    'password' => $this->password
                ]);
                $this->stream->request()->createUrlEncodedForm();
                break;
            case self::AUTH_FORM_DATA:
                $this->stream->setFields([
                    'username' => $this->username,
                    'password' => $this->password
                ]);
                $this->stream->request()->createMultipartForm();
                break;
            case self::AUTH_REFRESH:
                $headers['Authorization'] = 'Bearer ' . $this->bearerToken;
                $this->stream->setFields([$this->refreshTokenName => $this->refreshToken]);
                if ($this->contentType == 'application/json') {
                    $this->stream->request()->createAsJson();
                } else if ($this->contentType == 'application/x-www-form-urlencoded') {
                    $this->stream->request()->createUrlEncodedForm();
                } else if ($this->contentType == 'multipart/form-data') {
                    $this->stream->request()->createMultipartForm();
                }
                break;
        }

        if (!empty($headers)) {
            $this->stream->addRequestHeaders($headers);
        }
        if (!empty($contextOptions)) {
            $this->stream->setContextOptions($contextOptions);
        }
        if (!empty($contextParams)) {
            $this->stream->setContextParams($contextParams);
        }

        $this->stream->send();
        $this->result = (int)(($this->stream->hasResponse()) && ($this->stream->response()->getCode() == 200));
        return $this->result;
    }

    /**
     * Parse the scheme
     *
     * @param  string $wwwAuth
     * @return string
     */
    public function parseScheme($wwwAuth)
    {
        $type = null;
        if (strpos($wwwAuth, ' ') !== false) {
            $type   = substr($wwwAuth, 0, strpos($wwwAuth, ' '));
            $scheme = explode(', ', substr($wwwAuth, (strpos($wwwAuth, ' ') + 1)));

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
            $type = $wwwAuth;
        }

        return $type;
    }

    /**
     * Create auth digest header string
     *
     * @throws Exception
     * @return string
     */
    public function createDigest()
    {
        $relativeUri = $this->stream->getUrl();
        if (strpos($relativeUri, '://') !== false) {
            $relativeUri = substr($relativeUri, (strpos($relativeUri, '://') + 3));
        }
        $relativeUri = substr($relativeUri, strpos($relativeUri, '/'));

        $scheme = $this->getScheme();

        if (!isset($scheme['realm']) || !isset($scheme['nonce'])) {
            throw new Exception('Error: The realm and/or the nonce was not successfully parsed.');
        }

        $a1 = md5($this->username . ':' . $scheme['realm'] . ':' . $this->password);
        $a2 = md5($this->stream->getMethod() . ':' . $relativeUri);
        $r  = md5($a1 . ':' . $scheme['nonce'] . ':' . $a2);

        return 'Digest username="' . $this->username .
            '", realm="' . $scheme['realm'] . '", nonce="' . $scheme['nonce'] .
            '", uri="' . $relativeUri . '", response="' . $r . '"';
    }

}
