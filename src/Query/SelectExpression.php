<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use preg_match;
use ValueError;

/**
 * SELECT [expression]
 *
 * @phpstan-type SelectFromArray array{
 *      expression: string,
 *      alias?: string|null
 * }
 *
 * @phpstan-type SelectToArray array{
 *      expression: string,
 *      alias: string|null
 * }
 */
class SelectExpression extends Expression
{
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

    /** @return SelectToArray */
    public function toArray(): array
    {
        return [
            'expression'    => $this->expression,
            'alias'         => $this->alias
        ];
    }
}
