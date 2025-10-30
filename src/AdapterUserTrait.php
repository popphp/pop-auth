<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Auth;

/**
 * Adapter field trait
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.3
 */
trait AdapterUserTrait
{

    /**
     * Username field
     * @var string
     */
    protected string $usernameField = 'username';

    /**
     * Password field
     * @var string
     */
    protected string $passwordField = 'password';

    /**
     * User data
     * @var mixed
     */
    protected mixed $user = null;

    /**
     * Set the username field
     *
     * @param  string $usernameField
     * @return static
     */
    public function setUsernameField(string $usernameField): static
    {
        $this->usernameField = $usernameField;
        return $this;
    }

    /**
     * Set the password field
     *
     * @param  string $passwordField
     * @return static
     */
    public function setPasswordField(string $passwordField): static
    {
        $this->passwordField = $passwordField;
        return $this;
    }

    /**
     * Get the username field
     *
     * @return string
     */
    public function getUsernameField(): string
    {
        return $this->usernameField;
    }

    /**
     * Get the password field
     *
     * @return string
     */
    public function getPasswordField(): string
    {
        return $this->passwordField;
    }

    /**
     * Get the user record
     *
     * @return mixed
     */
    public function getUser(): mixed
    {
        return $this->user;
    }

}
