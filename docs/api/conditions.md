# Conditions

Conditions are used in `WHERE` and `HAVING` clauses. A basic condition is expressed as `expression` `operator` `value`.

## Simple condition

```php
<?php
Blrf\Dbal\Query\Condition(
    string $expression,
    string $operator = '=',
    string $value = '?'
);
```

You can construct a simple condition via [QueryBuilder](querybuilder.md)->condition():

```php
<?php
$queryBuilder->select(1)->where($queryBuilder->condition(1, '=', 1));
```

Output

```sql
SELECT 1 WHERE 1 = 1
```

## Condition builder

For more complex conditions you should use `Blrf\Dbal\Query\ConditionBuilder`.

You can start condition builder via [QueryBuilder](querybuilder.md) by providing callback function to `where()` or `having()` method:

```php
<?php
$queryBuilder
    ->select(1)
    ->where(
        fn($cb) => $cb->eq(1, 1)
    );
```

Output

```sql
SELECT 1 WHERE 1 = 1
```

You can use condition builder to create groups of conditions of two possible types: `AND` and `OR`.

```php
<?php
$queryBuilder->where(
    fn(Blrf\Dbal\Query\ConditionBuilder $cb) => $cb->or(
        $cb->and(
            $cb->eq('isbn13'),
            $cb->eq('language_id'),
        ),
        $cb->eq('title')
    )
);
```

Will create SQL condition:

```sql
((isbn13 = ? AND language_id = ?) OR title = ?)
```

### Operators

There are many predefined operators (methods) available:

- eq(): Equal `=`
- neq(): Not equal `<>`
- lt(): Less than `<`
- lte(): Less than or equal `<=`
- gt(): Greater than `>`
- gte(): Greater than or equal `>=`
- isNull(): `IS NULL`
- isNotNull(): `IS NOT NULL`

!!! note
    More should be added (LIKE, NOT LIKE, IN, NOT IN, ...)

And you may also use `condition()` method to construct any condition.

```php
<?php
$cb->condition('expression', 'operator', 'value');
```