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

/**
 * Auth interface
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.0
 */
interface AuthInterface
{

    /**
     * Get the authentication result
     *
     * @return int
     */
    public function getResult();

    /**
     * Determine if the authentication attempt was valid
     *
     * @return boolean
     */
    public function isValid();

    /**
     * Get the username
     *
     * @return string
     */
    public function getUsername();

    /**
     * Get the password
     *
     * @return string
     */
    public function getPassword();

    /**
     * Set the username
     *
     * @param  string $username
     * @return AuthInterface
     */
    public function setUsername($username);

    /**
     * Set the password
     *
     * @param  string $password
     * @return AuthInterface
     */
    public function setPassword($password);

    /**
     * Method to authenticate
     *
     * @param  string $username
     * @param  string $password
     * @return int
     */
    public function authenticate($username, $password);

    /**
     * Method to verify a password against a hash
     *
     * @param string $password
     * @param string $hash
     * @return boolean
     */
    public function verify($password, $hash);

}