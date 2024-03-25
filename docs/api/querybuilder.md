# Query builder

Query builder is a heart of every DBAL. It strives to support any SQL query.

## SELECT query

Select query is started with issuing `select()` method.

```php
<?php
$queryBuilder->select('column as A', 'column AS B');
```

SELECT queries are built from:

- [SELECT](#select-query) expression
- [FROM](#from-clause) expression
- [WHERE](#where) [conditions](conditions.md)
- [ORDER BY](#order-by) expression
- [LIMIT](#limit) expression

**Example**

```php
<?php
$queryBuilder
    ->select('column')
    ->from('table')
    ->where($queryBuilder->condition('column', '=', '?'))
    ->orderBy('column', 'ASC')
    ->limit(1)
    ->setParameters('condition');
```

!!! note
    See [Conditions](conditions.md) on how to construct `WHERE` conditions.

## UPDATE query

Update query is started by issuing `update()` method.

```php
<?php
$queryBuilder->update('table', 'alias');
```

UPDATE queries are built from:

- [FROM](#from-clause) table to update
- [SET](#set) expression
- [WHERE](#where) [conditions](conditions.md)
- [ORDER BY](#order-by) expression
- [LIMIT](#limit) expression

**Example**

```php
<?php
$queryBuilder
    ->update('table')
    ->set([
        'columnA' => 'valueA',
        'columnB' => 'valueB'
    ])
    ->where($queryBuilder->condition('column', '=', '?'))
    ->orderBy('column', 'ASC')
    ->limit(1)
    ->setParameters('condition');
```

## INSERT query

Insert query is started by issuing `insert()` method.

```php
<?php
$queryBuilder->insert('table', 'alias');
```

INSERT queries are built from:

- [INTO](#into-clause) table to insert into
- [COLUMNS](#values) columns definition
- [VALUES](#values) values

**Example**

```php
<?php
$queryBuilder
    ->insert('table')
    ->values([
        'columnA'   => 'valueA',
        'columnB'   => 'valueB'
    ]);
```

## DELETE query

Delete query is started by issuing `delete()` method.

```php
<?php
$queryBuilder->delete('table', 'alias');
```

DELETE queries are built from:

- [FROM](#from-clause) table to delete from
- [WHERE](#where) [conditions](conditions.md)
- [ORDER BY](#order-by) expressions
- [LIMIT](#limit) expressions

**Example**

```php
<?php
$queryBuilder
    ->delete('table')
    ->where($queryBuilder->condition('column', '=', '?'))
    ->orderBy('column', 'ASC')
    ->limit(1)
    ->setParameters('condition');
```

## FROM clause

```php
<?php
public function from(string|QueryBuilderInterface $from, string $as = null): static;
```

`FROM` clause defines a table or `subquery` (another `QueryBuilder` for SELECT statements).

## INTO clause

```php
<?php
public function into(string $from, string $as = null): static;
```

`INTO` clause defines a table for [INSERT](#insert-query) queries. It's an alias for [FROM](#from-clause), but it supports only table names and alias.

## VALUES

Values are an associative array of column, value for [INSERT](#insert-query) or [UPDATE](#update-query) queries.

```php
<?php
public function values(array $values): static;
public function value(string $column, mixed $value): static; // add single value
public function set(array $values): static; // alias for values
```