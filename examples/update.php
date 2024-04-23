<?php

require __DIR__ . '/../vendor/autoload.php';
$config  = new Blrf\Dbal\Config('mysql://user:pass@localhost/bookstore');

$config->create()->then(
    function (Blrf\Dbal\Connection $db) {
        // start query builder
        $qb = $db->query()
            ->update('book')
            ->values(['title' => 'Moby Dick: ' . time()]); // automatically added to parameters
        $qb->where($qb->condition('book_id'))
            ->addParameter([11089]) // we add parameter
            ->limit(1);
        echo "sql: " . $qb->getSql() . "\n";
        // sql: UPDATE book SET title = ? WHERE book_id = ? LIMIT 1
        return $qb->execute();
    }
)->then(
    function (Blrf\Dbal\Result $result) {
        print_r($result);
    }
);
