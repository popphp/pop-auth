<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth;

use Pop\Http\Client;
use Pop\Http\Auth;

/**
 * Http auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Http extends AbstractAuth
{

    use AdapterUserTrait;

    /**
     * Auth client 
     * @var Client
     */
    protected $client = null;

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
     * Constructor
     *
     * Instantiate the Http auth adapter object
     *
     */
    public function __construct()
    {
        $arguments = func_get_args();

        foreach ($arguments as $argument) {
            if ($argument instanceof Client) {
                $this->setClient($argument);
            } else if (($argument instanceof Auth) && ($this->client !== null)) {
                $this->client->setAuth($argument);
            }
        }
    }

    /**
     * Set client
     *
     * @param  Client $client
     * @return Http
     */
    public function setClient(Client $client): Http
    {
        $this->client = $client;
        return $this;
    }

    /**
     * Set the username
     *
     * @param  string $username
     * @return Http
     */
    public function setUsername(string $username): Http
    {
        parent::setUsername($username);

        if ($this->client !== null) {
            if ($this->client->hasAuth()) {
                $this->client->getAuth()->setUsername($username);
            } else {
                $this->client->addData($this->usernameField, $username);
            }
        }

        return $this;
    }

    /**
     * Set the password
     *
     * @param  string $password
     * @return Http
     */
    public function setPassword(string $password): Http
    {
        parent::setPassword($password);

        if ($this->client !== null) {
            if ($this->client->hasAuth()) {
                $this->client->getAuth()->setPassword($password);
            } else {
                $this->client->addData($this->passwordField, $password);
            }
        }

        return $this;
    }

    /**
     * Set the refresh token
     *
     * @param  string $refreshToken
     * @return Http
     */
    public function setRefreshToken(string $refreshToken): Http
    {
        $this->refreshToken = $refreshToken;

        if ($this->client !== null) {
            $this->client->setData([$this->refreshTokenName => $this->refreshToken]);
        }

        return $this;
    }

    /**
     * Set the refresh token name
     *
     * @param  string $refreshTokenName
     * @return Http
     */
    public function setRefreshTokenName(string $refreshTokenName): Http
    {
        $this->refreshTokenName = $refreshTokenName;
        return $this;
    }

    /**
     * Get client
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Get client (alias method)
     *
     * @return Client
     */
    public function client(): Client
    {
        return $this->client;
    }

    /**
     * Get result response
     *
     * @return mixed
     */
    public function getResultResponse(): mixed
    {
        $resultResponse = null;
        if (($this->client->hasResponse()) && ($this->client->getResponse()->hasBody())) {
            $resultResponse = $this->client->getResponse()->getParsedResponse();
        }
        return $resultResponse;
    }

    /**
     * Get the refresh token
     *
     * @return string
     */
    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    /**
     * Get the refresh token name
     *
     * @return string
     */
    public function getRefreshTokenName(): string
    {
        return $this->refreshTokenName;
    }

    /**
     * Has client
     *
     * @return bool
     */
    public function hasClient(): bool
    {
        return ($this->client !== null);
    }

    /**
     * Has refresh token
     *
     * @return bool
     */
    public function hasRefreshToken():bool
    {
        return ($this->refreshToken !== null);
    }

    /**
     * Has refresh token name
     *
     * @return bool
     */
    public function hasRefreshTokenName(): bool
    {
        return ($this->refreshTokenName !== null);
    }

    /**
     * Method to authenticate
     *
     * @param  ?string $username
     * @param  ?string $password
     * @return int
     */
    public function authenticate(?string $username = null, ?string $password = null): int
    {
        if ($username !== null) {
            $this->setUsername($username);
        }
        if ($password !== null) {
            $this->setPassword($password);
        }

        $this->client->send();
        $this->result = (int)(($this->client->hasResponse()) && ($this->client->getResponse()->isSuccess() == 200));

        $response = $this->getResultResponse();

        if (!empty($response) && is_array($response) && isset($response[$this->usernameField])) {
            $this->user = $response;
        }

        return $this->result;

    }

}
