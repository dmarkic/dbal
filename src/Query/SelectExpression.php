<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use preg_match;
use ValueError;

/**
 * SELECT [expression]
 */
class SelectExpression extends Expression
{
    /**
     * Create select expression from array
     *
     * Data keys:
     *
     * - expression: string
     * - alias: ?string
     *
     * @param array{expression?:string, alias?:string} $data
     */
    public static function fromArray(array $data): static
    {
        return new static($data['expression'] ?? '', $data['alias'] ?? null);
    }

    /**
     * Create select expression from string
     *
     * @note regexp is very basic
     */
    public static function fromString(string $string): static
    {
        $expression = '';
        $alias = null;
        if (preg_match('/(.+?)\W+AS\W+(.*)/iu', $string, $matches)) {
            $expression = trim($matches[1]);
            $alias = trim($matches[2]);
        } else {
            $expression = $string;
        }
        return new static($expression, $alias);
    }

    /**
     * Create select expression
     *
     */
    final public function __construct(
        public readonly string $expression,
        public readonly ?string $alias = null
    ) {
        if (strlen($this->expression) == 0) {
            throw new ValueError('Expression cannot be empty');
        }
    }

    public function __toString(): string
    {
        $ret = $this->expression;
        if ($this->alias !== null) {
            $ret .= ' AS ' . $this->alias;
        }
        return $ret;
    }

    /** @return array{expression: string, alias: string|null} */
    public function toArray(): array
    {
        return [
            'expression'    => $this->expression,
            'alias'         => $this->alias
        ];
    }
}
