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

/**
 * Auth interface
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
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
     * Determine if the authentication attempt was successful
     *
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * Determine if the last verified hash should be rehashed
     *
     * @return bool
     */
    public function needsRehash(): bool;

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
     * @param  string  $credential
     * @param  ?string $secondary
     * @throws Exception
     * @return int
     */
    public function authenticate(string $credential, ?string $secondary = null): int;

    /**
     * Method to verify a password against a hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verify(string $password, string $hash): bool;

}
