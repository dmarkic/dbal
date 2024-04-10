# Changelog

## main-dev (2024-03-17)

- QueryBuilder and Condition/ConditionGroup now have `fromArray()` and `toArray()` methods.

## main-dev (2024-03-25)

- Offical docs page: https://blrf.net/dbal/
- Full coverage
- Condtion/ConditionBuilder support for more operators (lg, lge, like, isNull, ...)

## main-dev (2024-04-04)

- PHPStan on max

## v1.0.0 (2024-04-10)

- QueryBuilder::fromArray() will probably be deprecated
- QueryBuilder::select() accepts SelectExpression
- QueryBuilder::from() accepts FromExpression
- QueryBuilder::createSelectException() correctly calls SelectExpression::fromString()
- Update QueryBuilderInterface
- Fix Result $rows param
- Test QueryBuilder::select() with SelectExpression
