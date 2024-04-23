# Result

Every query executed will return `Blrf\Dbal\Result` object.

```php
<?php
class Result {
    public readonly array $rows = [];
    public readonly ?int $insertId = null;
    public readonly int $affectedRows = 0;
    public readonly int $warningCount = 0;
}
```

## $rows

`SELECT` queries will return result with `$rows` array. 

### Iterator

Result implements [Iterator](https://www.php.net/iterator) so you can also use Result object as an array:

```php
<?php
foreach ($result as $row) {
    echo " - my awesome row: " . print_r($row, true) . "\n";
}
```

## $insertId

`INSERT` queries with sequence/autoincrement/... table keys will return last insert id.

## $affectedRows

`UPDATE` or `DELETE` queries will report affected rows.