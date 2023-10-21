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
 * Ldap auth class
 *
 * @category   Pop
 * @package    Pop\Auth
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2024 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
class Ldap extends AbstractAuth
{

    /**
     * Ldap host
     * @var ?string
     */
    protected ?string $host = null;

    /**
     * Ldap port
     * @var string|int|null
     */
    protected string|int|null $port = null;

    /**
     * Ldap options
     * @var array
     */
    protected array $options = [];

    /**
     * Ldap resource
     * @var mixed
     */
    protected $resource = null;

    /**
     * Constructor
     *
     * Instantiate the Ldap auth adapter object
     *
     * @param  string          $host
     * @param  string|int|null $port
     * @param  ?array          $options
     */
    public function __construct(string $host, string|int|null $port = null, ?array $options = null)
    {
        $this->setHost($host);
        if ($port !== null) {
            $this->setPort($port);
        }

        if (!empty($host)) {
            $host = ($this->port !== null) ? $this->host . ':' . $this->port : $this->host;
            $this->resource = ldap_connect($host);
        }

        if ($options !== null) {
            $this->setOptions($options);
        }
    }

    /**
     * Set the host
     *
     * @param  string $host
     * @return Ldap
     */
    public function setHost(string $host): Ldap
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Set the port
     *
     * @param  string|int $port
     * @return Ldap
     */
    public function setPort(string|int $port): Ldap
    {
        $this->port = $port;
        return $this;
    }

    /**
     * Set an option
     *
     * @param  mixed $option
     * @param  mixed $value
     * @return Ldap
     */
    public function setOption(mixed $option, mixed $value): Ldap
    {
        $this->options[$option] = $value;
        ldap_set_option($this->resource, $option, $value);

        return $this;
    }

    /**
     * Set options
     *
     * @param  array $options
     * @return Ldap
     */
    public function setOptions(array $options): Ldap
    {
        foreach ($options as $option => $value) {
            $this->setOption($option, $value);
        }

        return $this;
    }

    /**
     * Get the host
     *
     * @return ?string
     */
    public function getHost(): ?string
    {
        return $this->host;
    }

    /**
     * Get the port
     *
     * @return string|int|null
     */
    public function getPort(): string|int|null
    {
        return $this->port;
    }

    /**
     * Get an option
     *
     * @param  mixed $option
     * @return mixed
     */
    public function getOption(mixed $option): mixed
    {
        return $this->options[$option] ?? null;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get the Ldap resource
     *
     * @return mixed
     */
    public function getResource(): mixed
    {
        return $this->resource;
    }

    /**
     * Get the Ldap resource (alias)
     *
     * @return mixed
     */
    public function resource(): mixed
    {
        return $this->resource;
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

        $this->result = ($this->resource !== null) ?
            (int)(@ldap_bind($this->resource, $this->username, $this->password)) : 0;

        return $this->result;
    }

}