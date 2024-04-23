<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use ValueError;
use preg_match;

/**
 * Limit
 *
 * MySQL: You can not really use offset without limit. If you need it, set offset and limit to PHP_INT_MAX.
 * PostgreSql: Limit may be ALL
 *
 * @phpstan-type LimitFromArray array{
 *      limit?: int|null,
 *      offset?: int|null
 * }
 *
 * @phpstan-type LimitToArray array{
 *      limit: int|null,
 *      offset: int|null
 * }
 */
class Limit extends Expression
{
    protected ?int $limit = null;
    protected ?int $offset = null;

    /**
     * Construct new Limit clause
     *
     * @throws ValueError if both arguments are null
     */
    final public function __construct(?int $limit, ?int $offset = null)
    {
        if ($limit === null && $offset === null) {
            throw new ValueError('Both limit and offset are not set');
        }
        $this->limit = $limit;
        $this->offset = $offset;
    }

    /**
     * Return LIMIT SQL
     *
     * LIMIT row_count OFFSET offset
     */
    public function __toString(): string
    {
        $ret = '';
        if ($this->limit !== null) {
            $ret = 'LIMIT ' . $this->limit;
        }
        if ($this->offset !== null) {
            $ret .= ' OFFSET ' . $this->offset;
        }
        return $ret;
    }

    /** @return LimitToArray */
    public function toArray(): array
    {
        return ['limit' => $this->limit, 'offset' => $this->offset];
    }
}
