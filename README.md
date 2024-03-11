# Async DBAL

Async database abstraction layer for [ReactPHP](https://reactphp.org/).

> **Development version**: This project is currently in development.
> Currently this is a proof-of-concept for ReactPHP ORM that will use this DBAL.

**Table of contents**

* [Example](#example)
* [Streaming example](#streaming-example)
* [Usage](#usage)
  * [Config](#config)
  * [QueryBuilder](#query-builder)
  * [ConditionBuilder](#condition-builder)
  * [Result](#result)
  * [ResultStream](#resultstream)
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

Query builder for connection is obtained via `Connection::query()` method.

#### SELECT query

Select query is started with `$queryBuilder->select('column AS A', 'column AS B', '...more...')`.

```php
public function select(string ...$exprs): static;
```

Example query:

```php
$qb = new QueryBuilder();
$qb
    ->select('a', 'b')
    ->from('c')
    ->where(
        fn($b) => $b->eq('d')
    )
    ->orderBy('e')
    ->limit(1, 2)
    ->setParameters(['f']);
```

Query builder will generate SQL: `SELECT a,b FROM c WHERE d = ? ORDER BY e ASC LIMIT 1 OFFSET 2`.

#### UPDATE query

Update query is started with `$queryBuilder->update($from)`.

```php
public function update(string|self $from): static;
```

See `from()`.

Example query:

```php
$qb = new QueryBuilder();
$qb
    ->update('a')
    ->set([
        'b' => 'c',
        'd' => 'e'
    ])->where(
        $qb->condition('f')
    )->addParameter('g')
    ->orderBy('h')
    ->limit(1);
```

Query builder will generate SQL: `UPDATE a SET b = ?, d = ? WHERE f = ? ORDER BY h ASC LIMIT 1`.

#### INSERT query

Insert query is started with `$queryBuilder->insert($info)`.

```php
public function insert(string|self $info): static;
```

See `into()`.

Example query:

```php
$qb = new QueryBuilder();
$qb
    ->insert('a')
    ->values([
        'b' => 'c',
        'd' => 'f'
    ]);
```

Query builder will generate SQL: `INSERT INTO a (b, d) VALUES(?, ?)`.

#### DELETE query

Delete query is started with `$queryBuilder->delete($from)`.

```php
public function delete(string|self $from): static;
```

See `from()`.

#### from()

```php
public function from(string|self $from, string $as = null): static;
```

Create `FROM` SQL expression with optional `as` alias. You can also provide another `QueryBuilder` as `$from` to create a subquery.

#### value() and values()

Create value(s) for `UPDATE` and `INSERT`.

```php
    public function value(string $column, mixed $value): static;
    /**
     * Add values for insert or update
     *
     * Array: [ column => value, ...]
     */
    public function values(array $values): static;
```

It automatically adds values as parameters.

#### where()

```php
public function where(Condition|ConditionGroup|callable $condition): static;
```

See [ConditionBuilder](#condition-builder).

By providing a callable parameter as $condition, it will start a new [ConditionBuilder](#condition-builder).

For simple `WHERE` conditions, you can provide `Condition` or `ConditionGroup` directly.

#### andWhere()

```php
public function andWhere(Condition|ConditionGroup|callable $condition): static;
```

Add `AND` condition. By providing a callable parameter as $condition, it will start a new [ConditionBuilder](#condition-builder).

#### orWhere()

```php
public function orWhere(Condition|ConditionGroup|callable $condition): static;
```

Add `OR` condition. By providing a callable parameter as $condition, it will start a new [ConditionBuilder](#condition-builder).

#### condition()

```php
public function condition(string $expression = null, string $operator = '=', string $value = '?'): Condition|ConditionBuilder;
```

Create a simple condition or start [ConditionBuilder](#condition-builder).

#### orderBy()

Add `ORDER BY` expression.

```php
public function orderBy(string $orderBy, string $type = 'ASC'): static;
```

#### limit()

Add `LIMIT` and/or `OFFSET` expression.

```php
public function limit(?int $offset = null, ?int $limit = null): static;
```

#### Parameters

Parameters are handlded as regular indexed array, so it is important to know how how parameters are used in query.

##### setParameters()

This method will set parameters (override previous set parameters).

```php
public function setParameters(array $params): static;
```

##### addParameter()

Append one or more parameters.

```php
public function addParameter(mixed ...$param): static;
```

#### getParameters()

Get all parameters.

```php
public function getParameters(): array;
```

### Condition builder

Condition builder is used to build condition for `WHERE` and `HAVING`.

Example on how to build complex conditions:

```php
$b = new ConditionBuilder();
// $c = ConditionGroup
$c = $b->and(
    $b->eq('a', 'b'),
    $b->eq('c', 'd'),
    $b->or(
        $b->eq('e', 'f'),
        $b->eq('g', 'h')
    )
);
```

Condition builder will generate SQL: `(a = b AND c = d AND (e = f OR g = h))`

#### and()

Create new `ConditionGroup` of type `AND`.

```php
public function and(Condition|ConditionGroup ...$condition): ConditionGroup
)
```

### or()

Create new `ConditionGroup` of type `OR`.

```php
public function or(Condition|ConditionGroup ...$condition): ConditionGroup
```

### condition()

Create new `Condition`.

```php
public function condition(string $expression, string $operator, string $value = '?'): Condition
```

### eq()

Create new equal `Condition`.

```php
public function eq(string $expression, string $value = '?'): Condition
```

#### Condition

A simple `Condition` consists of `expression`, `operator` and `value`.

#### ConditionGroup

`ConditionGroup` is a group of `Condition`s of `AND` or `OR` types.

### Result

`Result` class has 4 readonly properties:

- `array $rows`: Rows returned from query
- `int $insertId`: Last insert Id generated
- `int $affectedRows`: Number of affected rows in `UPDATE` or `DELETE` queries
- `int $warningCount`: Number of warnings query produced

### ResultStream

Result stream is returned for streaming queries.

It's a simple implementation of [ReadableStreamInterface](https://github.com/reactphp/stream#readablestreaminterface) or [ReactPHP Stream](https://github.com/reactphp/stream) package.

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
