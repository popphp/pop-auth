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

/**
 * Auth interface
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
interface AuthInterface
{

    /**
     * Get the authentication result
     *
     * @return int
     */
    public function getResult(): int;

    /**
     * Determine if the authentication attempt was valid
     *
     * @return bool
     */
    public function isValid(): bool;

    /**
     * Get the username
     *
     * @return ?string
     */
    public function getUsername(): ?string;

    /**
     * Get the password
     *
     * @return ?string
     */
    public function getPassword(): ?string;

    /**
     * Set the username
     *
     * @param  string $username
     * @return AuthInterface
     */
    public function setUsername(string $username): AuthInterface;

    /**
     * Set the password
     *
     * @param  string $password
     * @return AuthInterface
     */
    public function setPassword(string $password): AuthInterface;

    /**
     * Method to authenticate
     *
     * @param  string $username
     * @param  string $password
     * @return int
     */
    public function authenticate(string $username, string $password): int;

    /**
     * Method to verify a password against a hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verify(string $password, string $hash): bool;

}