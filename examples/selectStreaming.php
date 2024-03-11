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
