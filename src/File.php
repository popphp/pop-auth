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
 * File auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2019 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.1.0
 */
class File extends AbstractAuth
{

    /**
     * Auth file
     * @var string
     */
    protected $filename = null;

    /**
     * Auth realm
     * @var string
     */
    protected $realm = null;

    /**
     * Auth file delimiter
     * @var string
     */
    protected $delimiter = ':';

    /**
     * Constructor
     *
     * Instantiate the File auth adapter object
     *
     * @param  string $filename
     * @param  string $realm
     * @param  string $delimiter
     * @throws Exception
     */
    public function __construct($filename, $realm = null, $delimiter = ':')
    {
        $this->setFilename($filename);
        $this->realm     = $realm;
        $this->delimiter = $delimiter;
    }

    /**
     * Get the auth filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Get the auth realm
     *
     * @return string
     */
    public function getRealm()
    {
        return $this->realm;
    }

    /**
     * Get the auth file delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Set the auth filename
     *
     * @param  string $filename
     * @throws Exception
     * @return File
     */
    public function setFilename($filename)
    {
        if (!file_exists($filename)) {
            throw new Exception('The access file does not exist.');
        }
        $this->filename = $filename;
        return $this;
    }

    /**
     * Set the auth realm
     *
     * @param  string $realm
     * @return File
     */
    public function setRealm($realm)
    {
        $this->realm = $realm;
        return $this;
    }

    /**
     * Set the auth file delimiter
     *
     * @param  string $delimiter
     * @return File
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * Method to authenticate
     *
     * @param  string $username
     * @param  string $password
     * @return int
     */
    public function authenticate($username, $password)
    {
        $this->setUsername($username);
        $this->setPassword($password);

        $lines        = file($this->filename);
        $hash         = null;
        $this->result = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            $user = explode($this->delimiter, $line);
            if (isset($user[0]) && ($user[0] == $this->username)) {
                if ((null !== $this->realm) && (count($user) == 3)) {
                    if (($this->username == $user[0]) && ($user[1] == $this->realm)) {
                        $hash = $user[2];
                        break;
                    }
                } else if (count($user) == 2) {
                    if ($this->username == $user[0]) {
                        $hash = $user[1];
                        break;
                    }
                }
            }
        }

        if ((null !== $this->password) && (null !== $hash)) {
            $this->result = (int)$this->verify($this->password, $hash);
        }

        return $this->result;
    }

}