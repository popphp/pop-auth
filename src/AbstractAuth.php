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
 * Abstract auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.0
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
    protected $result = 0;

    /**
     * Authentication username
     * @var string
     */
    protected $username = null;

    /**
     * Authentication password
     * @var string
     */
    protected $password = null;

    /**
     * Get the authentication result
     *
     * @return int
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Determine if the authentication attempt was valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return ($this->result == 1);
    }

    /**
     * Get the username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get the password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the username
     *
     * @param  string $username
     * @return AbstractAuth
     */
    public function setUsername($username)
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
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Method to verify a password against a hash
     *
     * @param string $password
     * @param string $hash
     * @return boolean
     */
    public function verify($password, $hash)
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
    abstract public function authenticate($username, $password);

}