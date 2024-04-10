<?php

declare(strict_types=1);

namespace Blrf\Dbal\Query;

use Blrf\Dbal\QueryBuilderInterface;
use ValueError;
use preg_match;
use is_array;
use strlen;

/**
 * FROM [expression]
 */
class FromExpression extends Expression
{
    /**
     * Create FromExpression from array
     *
     * Data keys:
     *
     * - expression
     *   - string
     *   - array with key 'class' will call class::fromArray() which is expected to be a QueryBuilderInterface
     * - alias
     *
     * @param array{expression?:string|array<mixed>, alias?:string} $data
     */
    public static function fromArray(array $data): static
    {
        $expression = $data['expression'] ?? '';
        /**
         * Is it a subquery?
         */
        if (is_array($expression) && isset($expression['class'])) {
            $class = $expression['class'];
            $expression = $class::fromArray($expression);
        }
        $alias = $data['alias'] ?? null;
        return new static($expression, $alias);
    }

    /**
     * Create from expression from string
     *
     * @note Currently does not support subquery as QueryBuilder
     *       But could probably be done with `(...) AS x` match.
     *       Very basic regexp.
     */
    public static function fromString(string $from): static
    {
        $expression = '';
        $alias = null;
        if (preg_match('/(.*)( AS )+(.*)/iu', $from, $matches)) {
            $expression = $matches[1];
            $alias = $matches[3];
        } else {
            $expression = $from;
        }
        return new static($expression, $alias);
    }

    /**
     * From expression
     *
     * QueryBuilderInterface enabled subquery
     */
    final public function __construct(
        public readonly string|QueryBuilderInterface $expression,
        public readonly ?string $alias = null
    ) {
        if (is_string($expression) && strlen($expression) == 0) {
            throw new ValueError('Expression cannot be empty');
        }
    }

    public function __toString(): string
    {
        if ($this->expression instanceof QueryBuilderInterface) {
            return '(' . $this->expression->getSql() . ') AS ' . $this->alias;
        }
        return $this->expression . ($this->alias === null ? '' : ' AS ' . $this->alias);
    }

    /** @return array{expression:string|array<mixed>, alias: string|null} */
    public function toArray(): array
    {
        return [
            'expression'    => (
                $this->expression instanceof QueryBuilderInterface ? $this->expression->toArray() : $this->expression
            ),
            'alias'         => $this->alias
        ];
    }
}
