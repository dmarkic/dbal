<?php

namespace Blrf\Tests\Dbal;

use Blrf\Dbal\Connection;
use Blrf\Dbal\Config;
use Blrf\Dbal\Driver;
use PHPUnit\Framework\Attributes\CoversClass;
use InvalidArgumentException;

use function React\Promise\resolve;
use function React\Async\await;

#[CoversClass(Config::class)]
class ConfigTest extends TestCase
{
    public function testConstructorAndGetters()
    {
        $uri = 'mysql://user:pass@localhost:3306/db?param=yes';
        $config = new Config('mysql://user:pass@localhost:3306/db?param=yes');
        $this->assertSame($uri, (string)$config);
        $this->assertSame('mysql', $config->getDriver());
        $this->assertSame('localhost', $config->getHost());
        $this->assertSame(3306, $config->getPort());
        $this->assertSame('user', $config->getUser());
        $this->assertSame('pass', $config->getPass());
        $this->assertSame('db', $config->getDb());
        $this->assertSame('db', $config->getDatabase());
        $this->assertSame(['param' => 'yes'], $config->getParams());
    }

    public function testFromArray()
    {
        $uri = 'mysql://user:pass@localhost:3306/db?param=yes';
        $data = [
            'uri'   => $uri,
            'db'    => 'newDb'
        ];
        $config = Config::fromArray($data);
        $this->assertSame('mysql://user:pass@localhost:3306/newDb?param=yes', (string)$config);
    }

    public function testSetInvalidUrlThrowsInvalidArgumentException()
    {
        $this->expectException(InvalidArgumentException::class);
        $config = new Config('mysql://:3306');
    }

    public function testAddAndCreateDriver()
    {
        $driver = $this->createMock(Driver::class);
        Config::addDriver('test', $driver::class);
        $config  = new Config('test://localhost');
        $this->assertInstanceOf($driver::class, $config->createDriver());
        Config::removeDriver('test');
    }

    public function testCreateDriverWithCustomClass()
    {
        $driver = $this->createMock(Driver::class);
        $config = new Config('test://localhost');
        $config->setDriver($driver::class);
        $this->assertInstanceOf($driver::class, $config->createDriver());
    }

    public function testAddInvalidDriver()
    {
        $this->expectException(InvalidArgumentException::class);
        Config::addDriver('test1', \StdClass::class);
    }

    public function testAddDriverSchemeAlreadyExists()
    {
        $driver = $this->createMock(Driver::class);
        $ex = false;
        try {
            Config::addDriver('test', $driver::class);
            Config::addDriver('test', $driver::class);
        } catch (InvalidArgumentException $e) {
            $ex = true;
        }
        $this->assertTrue($ex);
        Config::removeDriver('test');
    }

    public function testCreate()
    {
        $connection = $this->createStub(Connection::class);
        $config = $this->getMockBuilder(Config::class)->onlyMethods(['createDriver'])->getMock();
        $driver = $this->createMock(Driver::class);
        $config->expects($this->once())->method('createDriver')->willReturn($driver);
        $driver->expects($this->once())->method('connect')->with($config)->willReturn(resolve($connection));
        $ret = await($config->create());
        $this->assertSame($connection, $ret);
    }
}
