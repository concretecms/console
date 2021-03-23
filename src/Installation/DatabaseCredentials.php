<?php


namespace Concrete\Console\Installation;

/**
 * Value class that stores detected database credentials
 *
 * @psalm-immutable
 */
class DatabaseCredentials
{

    /** @var string */
    protected $username;

    /** @var string */
    protected $password;

    /** @var string */
    protected $database;

    /** @var string */
    protected $hostname;

    /** @var string */
    protected $port;

    /** @var string */
    protected $charset;

    public function __construct(string $username, string $password, string $database, string $hostname, string $port, string $charset)
    {
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
        $this->hostname = $hostname;
        $this->port = $port;
        $this->charset = $charset;
    }

    public static function empty(): DatabaseCredentials
    {
        return new self('','','','','','');
    }

    public function isEmpty(): bool
    {
        return
            $this->username === '' &&
            $this->password === '' &&
            $this->database === '' &&
            $this->hostname === '' &&
            $this->port === '' &&
            $this->charset === '';
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getDatabase(): string
    {
        return $this->database;
    }

    /**
     * @return string
     */
    public function getHostname(): string
    {
        return $this->hostname;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

}
