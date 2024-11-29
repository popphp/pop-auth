<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth;

/**
 * Abstract auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.1
 */
abstract class AbstractAuth implements AuthInterface
{

    /**
     * Constant for auth result
     * @var int
     */
    const NOT_VALID = 0;
    const VALID     = 1;

    /**
     * Authentication result
     * @var int
     */
    protected int $result = 0;

    /**
     * Authentication username
     * @var ?string
     */
    protected ?string $username = null;

    /**
     * Authentication password
     * @var ?string
     */
    protected ?string $password = null;

    /**
     * Get the authentication result
     *
     * @return int
     */
    public function getResult(): int
    {
        return $this->result;
    }

    /**
     * Determine if the authentication attempt was successful
     *
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        return ($this->result == 1);
    }

    /**
     * Get the username
     *
     * @return ?string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * Get the password
     *
     * @return ?string
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * Set the username
     *
     * @param  string $username
     * @return AbstractAuth
     */
    public function setUsername(string $username): AbstractAuth
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Set the password
     *
     * @param  string $password
     * @return AbstractAuth
     */
    public function setPassword(string $password): AbstractAuth
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Method to verify a password against a hash
     *
     * @param  string $password
     * @param  string $hash
     * @return bool
     */
    public function verify(string $password, string $hash): bool
    {
        $info = password_get_info($hash);

        return ((($info['algo'] == 0) && ($info['algoName'] == 'unknown')) ?
            ($password === $hash) : password_verify($password, $hash));
    }

    /**
     * Method to authenticate
     *
     * @param  string $username
     * @param  string $password
     * @return int
     */
    abstract public function authenticate(string $username, string $password): int;

}
