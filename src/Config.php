<?php

declare(strict_types=1);

namespace Blrf\Dbal;

use Blrf\Dbal\Driver\DriverInterface;
use React\Promise\PromiseInterface;
use InvalidArgumentException;
use RuntimeException;
use Stringable;
use SensitiveParameter;

/**
 * DBAL Configuration and connection creator
 *
 *
 * ## Driver
 *
 * Currently these drivers are directly supported:
 *
 * - mysql
 *
 * You can define your own driver and set it as class name (eg: \MyProject\Driver) `Config::setDriver()`
 *
 * Or you can use Config::addDriver('scheme', 'class') and then use url scheme://.../
 */
class Config implements Stringable
{
    /**
     * Map of drivers implemented directly in this code.
     */
    protected static array $driverMap = [
        'mysql' => \Blrf\Dbal\Driver\Mysql\Driver::class
    ];
    /**
     * Dbal driver
     *
     * @see Config::setDriver()
     */
    protected string $driver = 'mysql';
    protected string $host = 'localhost';
    protected ?int $port = null;
    protected string $user = '';
    protected ?string $pass = null;
    protected ?string $db = null;
    protected array $params = [];

    /**
     * Create config from array
     *
     * Possible keys:
     *
     * - uri (will set other properties)
     * - driver
     * - host
     * - port
     * - user
     * - pass
     * - db
     * - params
     */
    public static function fromArray(
        #[SensitiveParameter]
        array $data
    ): self {
        $config = new self();
        $props = ['uri', 'driver', 'host', 'port', 'user', 'pass', 'db', 'params'];
        foreach ($props as $prop) {
            $method = 'set' . $prop;
            if (isset($data[$prop])) {
                $config->$method($data[$prop]);
            }
        }
        return $config;
    }

    /**
     * Add driver to map
     *
     * You can then construct uri with scheme://...
     *
     * @throws InvalidArgumentException If driver class is not implementing Driver interface
     * @throws InvalidArgumentException if driver scheme already exists
     */
    public static function addDriver(string $scheme, string $class)
    {
        $obj = new $class();
        if (!($obj instanceof Driver)) {
            throw new InvalidArgumentException('Invalid driver class: ' . $class);
        }
        if (isset(self::$driverMap[$scheme])) {
            throw new InvalidArgumentException('Driver scheme already exists: ' . $scheme);
        }
        self::$driverMap[$scheme] = $class;
    }

    public static function removeDriver(string $scheme): void
    {
        if (isset(self::$driverMap[$scheme])) {
            unset(self::$driverMap[$scheme]);
        }
    }

    public function __construct(string $uri = null)
    {
        if ($uri !== null) {
            $this->setUri($uri);
        }
    }

    public function __toString(): string
    {
        return $this->getUri();
    }

    /**
     * Create new connection
     *
     * @return PromiseInterface<Connection>
     */
    public function create(): PromiseInterface
    {
        return $this->createDriver()->connect($this);
    }

    /**
     * Set uri
     *
     * driver://user:pass@host:port/db?params...
     */
    public function setUri(
        #[SensitiveParameter]
        string $uri
    ): self {
        $parsed = parse_url($uri);
        if ($parsed === false) {
            throw new InvalidArgumentException('Uri is not valid: ' . $uri);
        }
        if (isset($parsed['scheme'])) {
            $this->setDriver($parsed['scheme']);
        }
        if (isset($parsed['host'])) {
            $this->setHost($parsed['host']);
        }
        if (isset($parsed['port'])) {
            $this->setPort($parsed['port']);
        }
        if (isset($parsed['user'])) {
            $this->setUser($parsed['user']);
        }
        if (isset($parsed['pass'])) {
            $this->setPass($parsed['pass']);
        }
        if (isset($parsed['path'])) {
            $this->setDb(ltrim($parsed['path'], '/'));
        }
        if (isset($parsed['query'])) {
            $params = [];
            parse_str($parsed['query'], $params);
            $this->setParams($params);
        }
        return $this;
    }

    public function getUri(): string
    {
        $uri = '';
        if (!empty($this->driver)) {
            $uri .= $this->driver . '://';
        }
        if (!empty($this->user) || !empty($this->pass)) {
            if (!empty($this->user)) {
                $uri .= $this->user;
            }
            if (!empty($this->pass)) {
                $uri .= ':' . $this->pass;
            }
            $uri .= '@';
        }
        $uri .= $this->host;
        if ($this->port !== null) {
            $uri .= ':' . $this->port;
        }
        if (!empty($this->db)) {
            $uri .= '/' . $this->db;
        }
        if (!empty($this->params)) {
            $uri .= '?' . http_build_query($this->params);
        }
        return $uri;
    }

    /**
     * Set driver
     *
     * You can set this to one of the mapped drivers or
     * as your own class name that implements `Blrf\Orm\Driver` interface.
     */
    public function setDriver(string $driver): self
    {
        $this->driver = $driver;
        return $this;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }

    public function createDriver(): Driver
    {
        $driver = $this->driver;
        if (!class_exists($driver)) {
            $class = self::$driverMap[$driver];
            if ($class === null) {
                throw new RuntimeException('No such driver: ' . $driver);
            }
        } else {
            $class = $driver;
        }
        return new $class();
    }

    public function setHost(string $host): self
    {
        $this->host = $host;
        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function setPort(?int $port): self
    {
        $this->port = $port;
        return $this;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;
        return $this;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setPass(
        #[SensitiveParameter]
        ?string $pass = null
    ): self {
        $this->pass = $pass;
        return $this;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function setDb(?string $db = null): self
    {
        $this->db = $db;
        return $this;
    }

    public function getDb(): ?string
    {
        return $this->db;
    }

    public function getDatabase(): ?string
    {
        return $this->db;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;
        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
