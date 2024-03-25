# Async DBAL

[![CI status](https://github.com/dmarkic/dbal/actions/workflows/ci.yml/badge.svg)](https://github.com/dmarkic/dbal/actions)

Async database abstraction layer for [ReactPHP](https://reactphp.org/).

> **Development version**: This project is currently in development.
> This is a proof-of-concept for [ReactPHP ORM](https://github.com/dmarkic/orm) that uses this DBAL.

Full example is available in [Bookstore respository](https://github.com/dmarkic/orm-bookstore-example).
Bookstore example uses [blrf/dbal](https://github.com/dmarkic/dbal), [blrf/orm](https://github.com/dmarkic/orm) and [framework-x](https://github.com/reactphp-framework/framework-x) to showcase current DBAL/ORM development.

DBAL documentation is available at [https://blrf.net/dbal/](https://blrf.net/dbal/).

**Table of contents**

* [Example](#example)
* [Streaming example](#streaming-example)
* [Usage](#usage)
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

## Streaming example

This method returns a readable stream that will emit each row of the result set as a `data` event.
It will only buffer data to complete a single row in memory and will not store the whole result set. This allows you to process result sets of unlimited size that would not otherwise fit into memory.

```php
require __DIR__ . '/../vendor/autoload.php';
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
        // sql: SELECT * FROM book WHERE ((isbn13 = ? AND language_id = ?) OR title = ?) LIMIT 3
        $stream = $qb->stream();
        $stream->on('data', function (array $row) {
            echo " - received row: \n";
            print_r($row);
        });
        $stream->on('error', function (\Throwable $e) {
            echo " ! error: " . $e->getMessage() . "\n";
        });
        $stream->on('close', function () {
            echo " - Stream done\n";
        });
    }
);
```

## Usage

Please see the [DBAL documentation site](https://blrf.net/dbal/).

## Install

```
composer require blrf/dbal:dev-main
```

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
- Schema manager
