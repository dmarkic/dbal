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
 */
class Limit extends Expression
{
    protected ?int $limit = null;
    protected ?int $offset = null;

    /**
     * Limit from array
     *
     * Data keys:
     *
     * - limit
     * - offset
     */
    public static function fromArray(array $data): static
    {
        $limit = isset($data['limit']) ? (int)$data['limit'] : null;
        $offset = isset($data['offset']) ? (int)$data['offset'] : null;
        return new static($limit, $offset);
    }

    /**
     * Limit from string
     *
     * Currently supports string:
     *
     * - LIMIT l OFFSET o
     */
    public static function fromString(string $data): static
    {
        $limit = null;
        $offset = null;
        preg_match('/LIMIT\s+(\d+)(\s+OFFSET\s+(\d+))?/i', $data, $matches);
        if (isset($matches[1])) {
            $limit = (int)$matches[1];
        }
        if (isset($matches[3])) {
            $offset = (int)$matches[3];
        }
        return new self($limit, $offset);
    }

    /**
     * Construct new Limit clause
     *
     * @throws ValueError if both arguments are null
     */
    public function __construct(?int $limit, ?int $offset = null)
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

    public function toArray(): array
    {
        return ['limit' => $this->limit, 'offset' => $this->offset];
    }
}
