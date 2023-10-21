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
 * Table auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Table extends AbstractAuth
{

    use AdapterUserTrait;

    /**
     * DB table name/class name
     * @var ?string
     */
    protected ?string $table = null;

    /**
     * Constructor
     *
     * Instantiate the File auth adapter object
     *
     * @param  string $table
     * @param  string $usernameField
     * @param  string $passwordField
     */
    public function __construct(string $table, string $usernameField = 'username', string $passwordField = 'password')
    {
        $this->table         = $table;
        if (!empty($usernameField)) {
            $this->setUsernameField($usernameField);
        }
        if (!empty($passwordField)) {
            $this->setPasswordField($passwordField);
        }
    }

    /**
     * Get the table name
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Set the table name
     *
     * @param  string $table
     * @return Table
     */
    public function setTable(string $table): Table
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Method to authenticate
     *
     * @param  string $username
     * @param  string $password
     * @return int
     */
    public function authenticate(string $username, string $password): int
    {
        $this->setUsername($username);
        $this->setPassword($password);

        $table        = $this->table;
        $this->result = 0;
        $this->user   = $table::findOne([
            $this->usernameField => $this->username
        ]);

        if (($this->password !== null) && isset($this->user->{$this->passwordField}) &&
            ($this->user->{$this->passwordField} !== null)) {
            $this->result = (int)$this->verify($this->password, $this->user->{$this->passwordField});
        }

        return $this->result;
    }

}