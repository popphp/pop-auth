<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth\Adapter;

/**
 * Table auth adapter class
 *
 * @category   Pop
 * @package    Pop_Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Table extends EncryptedAdapter
{

    /**
     * DB table name / class name
     * @var string
     */
    protected $table = null;

    /**
     * Username field
     * @var string
     */
    protected $usernameField = 'username';

    /**
     * Password field
     * @var string
     */
    protected $passwordField = 'password';

    /**
     * DB table name / class name
     * @var \Pop\Db\Record
     */
    protected $user = null;

    /**
     * Constructor
     *
     * Instantiate the Table auth adapter object
     *
     * @param  string $table
     * @param  int    $encryption
     * @param  array  $options
     * @return Table
     */
    public function __construct($table, $encryption = 0, array $options = [])
    {
        $this->setTable($table);
        $this->setEncryption($encryption, $options);
    }

    /**
     * Get the table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get the username field
     *
     * @return string
     */
    public function getUsernameField()
    {
        return $this->usernameField;
    }

    /**
     * Get the password field
     *
     * @return string
     */
    public function getPasswordField()
    {
        return $this->passwordField;
    }

    /**
     * Get the user object
     *
     * @return \Pop\Db\Record
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the table name
     *
     * @param string $table
     * @return Table
     */
    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set the username field
     *
     * @param string $usernameField
     * @return Table
     */
    public function setUsernameField($usernameField = 'username')
    {
        $this->usernameField = $usernameField;
        return $this;
    }

    /**
     * Set the password field
     *
     * @param string $passwordField
     * @return Table
     */
    public function setPasswordField($passwordField = 'password')
    {
        $this->passwordField = $passwordField;
        return $this;
    }

    /**
     * Method to authenticate
     *
     * @return int
     */
    public function authenticate()
    {
        $table      = $this->table;
        $this->user = $table::findBy([
            $this->usernameField => $this->username
        ]);

        return (int)(isset($this->user->{$this->usernameField}) &&
            $this->verifyPassword($this->user->{$this->passwordField}, $this->password));
    }

}
