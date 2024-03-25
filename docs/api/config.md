# Config

`Blrf\Dbal\Config` is a DBAL configuration and connection creator.

```php title="Example"
<?php
$config = new Config('driver://hostname/database?param=value');
// create connection
$config->create()->then(
    function (Connection $db) {
        // work with connection
    }
);
```

## Setup

The easiest way to setup a new database connection is by using the Config construtor with $uri. Full Uri is passed on to the driver when creating new connection.

You can also use setter methods to create config

```php
<?php
$config = new Config();
$config
    ->setDriver('mysql')
    ->setHost('localhost')
    ->setPort(3306)
    ->setUser('user')
    ->setPass('password')
    ->setDb('database')
    ->setParams(['param' => 'value']);
echo $config->getUri(); // mysql://user:password@localhost:3306/database?param=value
```

## Adding driver

You can easily register new driver by calling `Config::addDriver()`, which you then initiate using the registered `scheme˙.

```php title="Add driver"
<?php
Config::addDriver('myDriver', '\\My\\Driver');
$config = new Config('myDriver://hostname/database?param=value');
```

## Remove driver

You can also remove driver:

```php title="Replace mysql driver"
<?php
Config::removeDriver('mysql');
Config::addDriver('mysql', '\\My\\Driver');
$config = new Config('myDriver://hostname/database?param=value');
```
