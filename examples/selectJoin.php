<?php

require __DIR__ . '/../vendor/autoload.php';
$config  = new Blrf\Dbal\Config('mysql://user:pass@localhost/bookstore');

$config->create()->then(
    function (Blrf\Dbal\Connection $db) {
        // start query builder
        $qb = $db->query()
            ->select('*')
            ->from('customer_address', 'address')
            ->join('address_status', 'address.status_id = status.status_id', 'status')
            ->where(
                fn(Blrf\Dbal\Query\ConditionBuilder $cb) => $cb->and(
                    $cb->eq('address.customer_id'),
                    $cb->like('status.address_status')
                )
            )
            ->setParameters([3, 'Inac%'])
            ->limit(4);
        echo "sql: " . $qb->getSql() . "\n";
        // sql: SELECT * FROM customer_address AS address
        //      INNER JOIN address_status AS status ON address.status_id = status.status_id
        //      WHERE (address.customer_id = ? AND status.address_status LIKE ?) LIMIT 4
        return $qb->execute();
    }
)->then(
    function (Blrf\Dbal\Result $result) {
        print_r($result->rows);
    }
);
