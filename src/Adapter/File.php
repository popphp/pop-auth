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
namespace Pop\Auth\Adapter;

/**
 * File auth adapter class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class File extends EncryptedAdapter
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
     * @param  int    $encryption
     * @param  array  $options
     * @return File
     */
    public function __construct($filename, $encryption = 0, array $options = [])
    {
        $this->setFilename($filename);
        $this->setEncryption($encryption, $options);
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
     * @param string $filename
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
     * @param string $realm
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
     * @param string $delimiter
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
     * @return int
     */
    public function authenticate()
    {
        $lines = file($this->filename);

        $result = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            $user = explode($this->delimiter, $line);
            if (isset($user[0]) && ($user[0] == $this->username)) {
                if ((null !== $this->realm) && (count($user) == 3)) {
                    $password = $user[2];
                    $string = $this->username . $this->delimiter . $this->realm . $this->delimiter . $password;
                    $result = (int)(($string == $line) && $this->verifyPassword($password, $this->password));
                } else if (count($user) == 2) {
                    $password = $user[1];
                    $string = $this->username . $this->delimiter . $password;
                    $result = (int)(($string == $line) && $this->verifyPassword($password, $this->password));
                }
            }
        }

        return $result;
    }

}
