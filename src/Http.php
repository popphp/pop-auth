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
 * @version    3.1.0
 */
class Http extends AbstractAuth
{

    /**
     * HTTP auth type constants
     */
    const AUTH_BASIC   = 'Basic';
    const AUTH_DIGEST  = 'Digest';
    const AUTH_BEARER  = 'Bearer';
    const AUTH_DATA    = 'Data';
    const AUTH_REFRESH = 'Refresh';

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
     * Auth response
     * @var Http\Response
     */
    protected $response = null;

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
            $this->setMethod($method);
        }

        if (null !== $type) {
            $this->setType($type);
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
     * Set method
     *
     * @param  string $method
     * @return Http
     */
    public function setMethod($method)
    {
        $this->method = $method;
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
     * Set auth response
     *
     * @param  Http\Response $response
     * @return Http
     */
    public function setResponse(Http\Response $response)
    {
        $this->response = $response;
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
     * Get method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
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
     * Get auth response
     *
     * @return Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Initialize the auth request
     *
     * @param  string $method
     * @return void
     */
    public function initRequest($method)
    {
        $response = new Http\Response();
        $response->sendRequest($this->uri, ['http' => ['method' => $method]]);

        // Check for the WWW Auth header and parse it
        if (null !== $response->getHeader('WWW-Authenticate')) {
            $this->type = $this->parseScheme($response->getHeader('WWW-Authenticate'));
        } else if (null !== $response->getHeader('WWW-authenticate')) {
            $this->type = $this->parseScheme($response->getHeader('WWW-authenticate'));
        } else if (null !== $response->getHeader('Www-Authenticate')) {
            $this->type = $this->parseScheme($response->getHeader('Www-Authenticate'));
        } else if (null !== $response->getHeader('www-authenticate')) {
            $this->type = $this->parseScheme($response->getHeader('www-authenticate'));
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
    public function authenticate($username, $password, array $headers = null, array $contextOptions = [], array $contextParams = null)
    {
        $this->setUsername($username);
        $this->setPassword($password);

        if ((null === $this->type) || (empty($this->scheme) && ($this->type == self::AUTH_DIGEST))) {
            $this->initRequest($this->method);
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
                $context['http']['header'] .= Http\AuthHeader::createDigest($this);
                break;
            case self::AUTH_BASIC:
                $context['http']['header'] .= Http\AuthHeader::createBasic($this);
                break;
            case self::AUTH_BEARER:
                $context['http']['header'] .= Http\AuthHeader::createBearer($this);
                break;
            case self::AUTH_DATA:
                $dataHeader = Http\AuthHeader::createData($this);
                $context['http']['header'] .= $dataHeader['header'];
                $context['http']['content'] = $dataHeader['data'];
                break;
            case self::AUTH_REFRESH:
                $refreshHeader = Http\AuthHeader::createRefresh($this, $headers);
                $context['http']['header'] .= $refreshHeader['header'];
                $context['http']['content'] = $refreshHeader['data'];
                break;
        }

        if (null === $this->response) {
            $this->response = new Http\Response();
        }

        $this->response->sendRequest($this->uri, $context, $contextParams);
        $this->result = (int)($this->response->getCode() == 200);

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

}