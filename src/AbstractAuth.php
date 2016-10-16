<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth;

/**
 * Auth abstract  class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
abstract class AbstractAuth implements AuthInterface
{

    /**
     * Encryption constants
     * @var int
     */
    const ENCRYPT_NONE          = 0;
    const ENCRYPT_MD5           = 1;
    const ENCRYPT_SHA1          = 2;
    const ENCRYPT_CRYPT         = 3;
    const ENCRYPT_BCRYPT        = 4;
    const ENCRYPT_MCRYPT        = 5;
    const ENCRYPT_CRYPT_MD5     = 6;
    const ENCRYPT_CRYPT_SHA_256 = 7;
    const ENCRYPT_CRYPT_SHA_512 = 8;

    /**
     * Constant for credentials not being valid
     * @var int
     */
    const NOT_VALID = 0;

    /**
     * Constant for credentials being valid
     * @var int
     */
    const VALID = 1;

    /**
     * Authentication result
     * @var int
     */
    protected $result = 0;

    /**
     * Username to authenticate against
     * @var string
     */
    protected $username = null;

    /**
     * Password to authenticate against
     * @var string
     */
    protected $password = null;

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
     * Method to authenticate
     *
     * @param  string $username
     * @param  string $password
     * @return int
     */
    abstract public function authenticate($username, $password);

}
