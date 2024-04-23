<?php

require __DIR__ . '/../vendor/autoload.php';
$config  = new Blrf\Dbal\Config('mysql://user:pass@localhost/bookstore');

$config->create()->then(
    function (Blrf\Dbal\Connection $db) {
        // start query builder
        $qb = $db->query()
            ->update('address')
            ->join(
                'customer_address',
                'customer_address.address_id = address.address_id'
            )
            ->join(
                'address_status',
                'address_status.status_id = customer_address.status_id'
            )
            ->values(['address.street_name' => 'Inactive: ' . time()]) // parameter is added
            ->where(
                fn($cb) => $cb->and(
                    $cb->eq('address_status.address_status'),
                    $cb->eq('customer_address.customer_id')
                )
            )
            ->addParameter('Inactive', 3);
        echo "sql: " . $qb->getSql() . "\n";
        // sql: UPDATE address INNER JOIN customer_address ON customer_address.address_id = address.address_id
        // INNER JOIN address_status ON address_status.status_id = customer_address.status_id
        // SET address.street_name = ?
        // WHERE (address_status.address_status = ? AND customer_address.customer_id = ?)
        return $qb->execute();
    }
)->then(
    function (Blrf\Dbal\Result $result) {
        print_r($result);
    }
);
