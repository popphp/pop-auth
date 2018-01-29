<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth;

/**
 * Table auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2018 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.5
 */
class Table extends AbstractAuth
{

    /**
     * DB table name/class name
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
     * User record
     * @var mixed
     */
    protected $user = null;

    /**
     * Constructor
     *
     * Instantiate the File auth adapter object
     *
     * @param  string $table
     * @param  string $usernameField
     * @param  string $passwordField
     * @throws Exception
     */
    public function __construct($table, $usernameField = 'username', $passwordField = 'password')
    {
        $this->table         = $table;
        $this->usernameField = $usernameField;
        $this->passwordField = $passwordField;
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
     * Get the user record
     *
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
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
        parent::authenticate($username, $password);

        $table        = $this->table;
        $this->result = 0;
        $this->user   = $table::findOne([
            $this->usernameField => $this->username
        ]);

        if ((null !== $this->password) && isset($this->user->{$this->passwordField}) && (null !== $this->user->{$this->passwordField})) {
            $this->result = (int)$this->verify($this->password, $this->user->{$this->passwordField});
        }

        return $this->result;
    }

}