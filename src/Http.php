<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth;

use Pop\Http\Client;
use Pop\Http\Client\Response;
use Pop\Http\Auth;

/**
 * Http auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
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
        $response       = $this->client->hasResponse() ? $this->client->getResponse() : null;
        if (($response instanceof Response) && $response->hasBody()) {
            $resultResponse = $response->getParsedResponse();
        }
        return $resultResponse;
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
     * Method to authenticate
     *
     * @param  ?string $username
     * @param  ?string $password
     * @throws Exception
     * @return int
     */
    public function authenticate(?string $username = null, ?string $password = null): int
    {
        $this->needsRehash = false;

        if (!$this->hasClient()) {
            throw new Exception('No HTTP client has been set.');
        }

        if ($username !== null) {
            $this->setUsername($username);
        }
        if ($password !== null) {
            $this->setPassword($password);
        }

        try {
            $this->client->send();
        } catch (\Throwable $e) {
            throw new Exception('Unable to send the HTTP authentication request: ' . $e->getMessage(), 0, $e);
        }

        $this->result = (int)(($this->client->hasResponse()) && ($this->client->getResponse()->isSuccess() == 200));

        $response = $this->getResultResponse();

        if (!empty($response) && is_array($response) && isset($response[$this->usernameField])) {
            $this->user = $response;
        }

        return $this->result;

    }

}
