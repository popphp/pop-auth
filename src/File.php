<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class File extends AbstractAuth
{

    /**
     * Auth file
     * @var ?string
     */
    protected ?string $filename = null;

    /**
     * Auth realm
     * @var ?string
     */
    protected ?string $realm = null;

    /**
     * Auth file delimiter
     * @var string
     */
    protected string $delimiter = ':';

    /**
     * Constructor
     *
     * Instantiate the File auth adapter object
     *
     * @param  string  $filename
     * @param  ?string $realm
     * @param  string  $delimiter
     * @throws Exception
     */
    public function __construct(string $filename, ?string $realm = null, string $delimiter = ':')
    {
        $this->setFilename($filename);
        if (!empty($realm)) {
            $this->setRealm($realm);
        }
        if (!empty($delimiter)) {
            $this->setDelimiter($delimiter);
        }
    }

    /**
     * Get the auth filename
     *
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Get the auth realm
     *
     * @return ?string
     */
    public function getRealm(): ?string
    {
        return $this->realm;
    }

    /**
     * Get the auth file delimiter
     *
     * @return string
     */
    public function getDelimiter(): string
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
    public function setFilename(string $filename): File
    {
        if (!file_exists($filename)) {
            throw new Exception("The access file '" . $filename . "' does not exist.");
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
    public function setRealm(string $realm): File
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
    public function setDelimiter(string $delimiter): File
    {
        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * Method to authenticate
     *
     * @param  string  $username
     * @param  ?string $password
     * @throws Exception
     * @return int
     */
    public function authenticate(string $username, ?string $password = null): int
    {
        $this->setUsername($username);

        $this->result      = 0;
        $this->needsRehash = false;

        if ($password === null) {
            return $this->result;
        }

        $this->setPassword($password);

        $lines = @file($this->filename);

        if ($lines === false) {
            throw new Exception("The access file '" . $this->filename . "' could not be read.");
        }

        $hash = null;

        foreach ($lines as $line) {
            $line = trim($line);
            $user = explode($this->delimiter, $line);
            if ($user[0] == $this->username) {
                if (($this->realm !== null) && (count($user) == 3)) {
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

        if (($this->password !== null) && ($hash !== null)) {
            $this->result = (int)$this->verify($this->password, $hash);
        }

        return $this->result;
    }

}
