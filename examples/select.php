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
            ->orderBy('publication_date', 'DESC')
            ->setParameters(['9789998691568', 1, 'Moby Dick'])
            ->limit(3);
        // sql: SELECT * FROM book WHERE ((isbn13 = ? AND language_id = ?) OR title = ?)
        //      ORDER BY publication_date DESC LIMIT 3
        return $qb->execute();
    }
)->then(
    function (Blrf\Dbal\Result $result) {
        print_r($result->rows);
        // or
        foreach ($result as $row) {
            print_r($row);
        }
    }
);
