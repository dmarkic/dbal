# Async DBAL

Async database abstraction layer for [ReactPHP](https://reactphp.org/).

> **Development version**: This project is currently in development.
> Currently this is a proof-of-concept for ReactPHP ORM that will use this DBAL.

**Table of contents**

* [Example](#example)
* [Usage](#usage)
  * [Config](#config)
  * [QueryBuilder](#querybuilder)
* [Install](#install)
* [Tests](#tests)
* [License](#license)

## Example

```php
<?php
require __DIR__ . '/vendor/autoload.php';
$config  = new Blrf\Dbal\Config('mysql://user:pass@localhost/bookstore');

$config->create()->then(
    function (Blrf\Dbal\Connection $db) {
        // start query builder
        $qb = $db->query()
            ->select('*')
            ->from('book')
            ->where(
                fn(Blrf\Dbal\Query\ConditionBuilder $cb) => $cb->or(
                    $cb->and(
                        $cb->eq('isbn13'),
                        $cb->eq('language_id'),
                    ),
                    $cb->eq('title')
                )
            )
            ->setParameters(['9789998691568', 1, 'Moby Dick'])
            ->limit(3);
        // $qb->getSql(): SELECT * FROM book WHERE ((isbn13 = ? AND language_id = ?) OR title = ?) LIMIT 3
        return $qb->execute();
    }
)->then(
    function (Blrf\Dbal\Result $result) {
        print_r($result->rows);
    }
);
```

## Usage

### Config

`Config` is a DBAL configuration and connection creator.

```php
$config = new Config('driver://hostname/database?param=value');
// create connection
$config->create()->then(
    function (Connection $db) {
        // work with connection
    }
);
```

#### Driver

Currently these drivers are directly supported:

- mysql: [ReactPHP Mysql](https://github.com/friends-of-reactphp/mysql/)

You can define your own driver and set it as class name (eg `MyProject\MyDbDriver`) with `Config::setDriver()`.

To use `scheme`, you can also register your driver with `Config::addDriver('mydriver', 'class')` and then use uri `mydriver://...`.

### Query builder

TBD;

## Write new driver

## Install

TBD;

## Tests

To run the test suite, go to project root and run:

```
vendor/bin/phpunit
```

## License

MIT, see [LICENSE file](LICENSE).

## Todo

- Write more examples
- Write having
- Write joins
- schemaManager
- streaming queries
