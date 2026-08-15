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
 * Table auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
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
     * @throws Exception
     * @return int
     */
    public function authenticate(string $username, string $password): int
    {
        $this->setUsername($username);
        $this->setPassword($password);

        $table             = $this->table;
        $this->result      = 0;
        $this->needsRehash = false;

        try {
            $this->user = $table::findOne([
                $this->usernameField => $this->username
            ]);
        } catch (\Throwable $e) {
            throw new Exception('Unable to query the user table: ' . $e->getMessage(), 0, $e);
        }

        if (isset($this->user->{$this->passwordField})) {
            $this->result = (int)$this->verify($this->password, $this->user->{$this->passwordField});
        }

        return $this->result;
    }

}
