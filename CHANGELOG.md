# Changelog

## v1.0.2 (2024-04-23)

- QueryBuilder::quoteIdentifier()
- QueryBuilder fromArray is not longer static
- QueryBuilder\Query - removed static fromArray methods (except in Conditions)
- Updated docs

## v1.0.1 (2024-04-21)

- QueryBuilder::join() added
- sqlite driver added EXPERIMENTAL (rather old version, not production ready)
- Connection::quit() method added

## v1.0.0 (2024-04-10)

- QueryBuilder::fromArray() will probably be deprecated
- QueryBuilder::select() accepts SelectExpression
- QueryBuilder::from() accepts FromExpression
- QueryBuilder::createSelectException() correctly calls SelectExpression::fromString()
- Update QueryBuilderInterface
- Fix Result $rows param
- Test QueryBuilder::select() with SelectExpression

## main-dev (2024-04-04)

- PHPStan on max

## main-dev (2024-03-25)

- Offical docs page: https://blrf.net/dbal/
- Full coverage
- Condtion/ConditionBuilder support for more operators (lg, lge, like, isNull, ...)

## main-dev (2024-03-17)

- QueryBuilder and Condition/ConditionGroup now have `fromArray()` and `toArray()` methods.
