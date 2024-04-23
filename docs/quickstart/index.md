# Quickstart example


!!! note
    These examples use the [Orm bookstore example](https://github.com/dmarkic/orm-bookstore-example) database available on GitHub.

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
        // or you can iterate result rows directly
        foreach ($result as $row) {
            print_r($row);
        }
    }
);
```

This example uses [Config](../api/config.md) to setup a Mysql connection to `bookstore` database and performs a simple query which returns [Result](../api/result.md) by calling driver `execute()` method.

## Streaming example

Streaming results are utilizing [react/stream](https://github.com/reactphp/stream) readable stream which emits each row of the result as a `data` event. It will only buffer data to complete a single row in memory and will not store the whole result set. This allows you to process result sets of unlimited size that would not otherwise fit into memory.
You can request streaming result by calling drivers `stream()` method.

```php
<?php
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

