<?php

namespace Blrf\Tests\Dbal\Query;

use Blrf\Tests\Dbal\TestCase;
use Blrf\Dbal\QueryBuilder;
use Blrf\Dbal\Query\Condition;
use Blrf\Dbal\Query\ConditionBuilder;
use Blrf\Dbal\Query\ConditionGroup;
use PHPUnit\Framework\Attributes\CoversClass;
use ValueError;

#[CoversClass(Condition::class)]
#[CoversClass(ConditionBuilder::class)]
#[CoversClass(ConditionGroup::class)]
class ConditionTest extends TestCase
{
    public function testConditionToString(): void
    {
        $c = new Condition('expr', 'op', 'value');
        $this->assertSame('expr op value', (string)$c);
        $exp = [
            'expression'    => 'expr',
            'operator'      => 'op',
            'value'         => 'value'
        ];
    }

    public function testConditionToAndFromArray(): void
    {
        $c = new Condition('expr', 'op', 'value');
        $exp = [
            'expression'    => 'expr',
            'operator'      => 'op',
            'value'         => 'value'
        ];
        $this->assertSame($exp, $c->toArray());
        $ca = Condition::fromArray($c->toArray());
        $this->assertSame($c->toArray(), $ca->toArray());
        $data = ['expression' => 'expr'];
        $c = Condition::fromArray($data);
        $this->assertSame('expr', $c->expression);
        $this->assertSame('=', $c->operator);
        $this->assertSame('?', $c->value);
        $data = ['expression' => 'expr', 'operator' => 'op'];
        $c = Condition::fromArray($data);
        $this->assertSame('expr', $c->expression);
        $this->assertSame('op', $c->operator);
        $this->assertSame('?', $c->value);
        $data = ['expression' => 'expr', 'operator' => 'op', 'value' => 'value'];
        $c = Condition::fromArray($data);
        $this->assertSame('expr', $c->expression);
        $this->assertSame('op', $c->operator);
        $this->assertSame('value', $c->value);
    }

    public function testConditionFromListArray(): void
    {
        $data = ['expr'];
        $c = Condition::fromArray($data);
        $this->assertSame('expr', $c->expression);
        $this->assertSame('=', $c->operator);
        $this->assertSame('?', $c->value);
        $data = ['expr', 'op'];
        $c = Condition::fromArray($data);
        $this->assertSame('expr', $c->expression);
        $this->assertSame('op', $c->operator);
        $this->assertSame('?', $c->value);
        $data = ['expr', 'op', 'value'];
        $c = Condition::fromArray($data);
        $this->assertSame('expr', $c->expression);
        $this->assertSame('op', $c->operator);
        $this->assertSame('value', $c->value);
    }

    public function testConditionFromArrayExpressionNotString()
    {
        $this->expectException(ValueError::class);
        $data['expression'] = [];
        $c = Condition::fromArray($data);
    }

    public function testConditionFromArrayOperatorNotString()
    {
        $this->expectException(ValueError::class);
        $data['expression'] = '1';
        $data['operator'] = [];
        $c = Condition::fromArray($data);
    }

    public function testConditionFromArrayValueNotString()
    {
        $this->expectException(ValueError::class);
        $data['expression'] = '1';
        $data['operator'] = 'eq';
        $data['value'] = [];
        $c = Condition::fromArray($data);
    }

    public function testConditionGroupToString(): void
    {
        $c = new ConditionGroup(
            'AND',
            new Condition('a', 'eq', 'b'),
            new Condition('c', 'eq', 'd')
        );
        $this->assertSame('(a eq b AND c eq d)', (string)$c);
    }

    public function testConditionGroupToAndFromArray(): void
    {
        $c = new ConditionGroup(
            'AND',
            new Condition('a', 'eq', 'b'),
            new Condition('c', 'eq', 'd')
        );
        $data = $c->toArray();
        $exp = [
            'AND'   => [
                [
                    'expression'    => 'a',
                    'operator'      => 'eq',
                    'value'         => 'b'
                ],
                [
                    'expression'    => 'c',
                    'operator'      => 'eq',
                    'value'         => 'd'
                ]
            ]
        ];
        $this->assertSame($exp, $data);
        $nc = ConditionGroup::fromArray($data);
        $this->assertSame($exp, $nc->toArray());
    }

    public function testConditionBuilderSimple(): void
    {
        $b = new ConditionBuilder();
        $c = $b->eq('expr', 'value');
        $this->assertSame('expr = value', (string)$c);
    }

    public function testConditionBuilderGroups(): void
    {
        $b = new ConditionBuilder();
        $c = $b->and(
            $b->eq('a', 'b'),
            $b->eq('c', 'd'),
            $b->or(
                $b->eq('e', 'f'),
                $b->eq('g', 'h')
            )
        );
        $this->assertSame('(a = b AND c = d AND (e = f OR g = h))', (string)$c);
        $exp = [
            'AND' => [
                [
                    'expression' => 'a',
                    'operator' => '=',
                    'value' => 'b'
                ],
                [
                    'expression' => 'c',
                    'operator' => '=',
                    'value' => 'd'
                ],
                [
                    'OR' => [
                        [
                            'expression' => 'e',
                            'operator' => '=',
                            'value' => 'f'
                        ],
                        [
                            'expression' => 'g',
                            'operator' => '=',
                            'value' => 'h'
                        ]
                    ]
                ]
            ]
        ];
        $data = $c->toArray();
        $this->assertSame($exp, $data);
        $nc = Condition::fromArray($data);
        $this->assertSame($exp, $nc->toArray());
    }

    public function testOperators(): void
    {
        $b = new ConditionBuilder();
        $c = $b->eq('expression', 'value');
        $this->assertSame('expression = value', (string)$c, 'eq operator toString error');
        $this->assertSame(
            [
                'expression'    => 'expression',
                'operator'      => '=',
                'value'         => 'value',
            ],
            $c->toArray(),
            'eq operator toArray error'
        );

        $c = $b->neq('expression', 'value');
        $this->assertSame('expression <> value', (string)$c, 'neq operator toString error');
        $this->assertSame(
            [
                'expression'    => 'expression',
                'operator'      => '<>',
                'value'         => 'value',
            ],
            $c->toArray(),
            'neq operator toArray error'
        );

        $c = $b->lt('expression', 'value');
        $this->assertSame('expression < value', (string)$c, 'lt operator toString error');
        $this->assertSame(
            [
                'expression'    => 'expression',
                'operator'      => '<',
                'value'         => 'value',
            ],
            $c->toArray(),
            'lt operator toArray error'
        );

        $c = $b->lte('expression', 'value');
        $this->assertSame('expression <= value', (string)$c, 'lte operator toString error');
        $this->assertSame(
            [
                'expression'    => 'expression',
                'operator'      => '<=',
                'value'         => 'value',
            ],
            $c->toArray(),
            'lte operator toArray error'
        );

        $c = $b->gt('expression', 'value');
        $this->assertSame('expression > value', (string)$c, 'gt operator toString error');
        $this->assertSame(
            [
                'expression'    => 'expression',
                'operator'      => '>',
                'value'         => 'value',
            ],
            $c->toArray(),
            'gt operator toArray error'
        );

        $c = $b->gte('expression', 'value');
        $this->assertSame('expression >= value', (string)$c, 'gte operator toString error');
        $this->assertSame(
            [
                'expression'    => 'expression',
                'operator'      => '>=',
                'value'         => 'value',
            ],
            $c->toArray(),
            'gte operator toArray error'
        );

        $c = $b->isNull('expression');
        $this->assertSame('expression IS NULL', (string)$c, 'isNull operator toString error');
        $this->assertSame(
            [
                'expression'    => 'expression',
                'operator'      => 'IS NULL',
                'value'         => null,
            ],
            $c->toArray(),
            'isNull operator toArray error'
        );

        $c = $b->isNotNull('expression');
        $this->assertSame('expression IS NOT NULL', (string)$c, 'isNotNull operator toString error');
        $this->assertSame(
            [
                'expression'    => 'expression',
                'operator'      => 'IS NOT NULL',
                'value'         => null,
            ],
            $c->toArray(),
            'isNotNull operator toArray error'
        );

        $c = $b->like('expression', 'value');
        $this->assertSame('expression LIKE value', (string)$c, 'like operator toString error');
        $this->assertSame(
            [
                'expression'    => 'expression',
                'operator'      => 'LIKE',
                'value'         => 'value',
            ],
            $c->toArray(),
            'like operator toArray error'
        );

        $c = $b->notLike('expression', 'value');
        $this->assertSame('expression NOT LIKE value', (string)$c, 'notLike operator toString error');
        $this->assertSame(
            [
                'expression'    => 'expression',
                'operator'      => 'NOT LIKE',
                'value'         => 'value',
            ],
            $c->toArray(),
            'notLike operator toArray error'
        );
    }

    public function testConditionFromArrayExpressionIsNullThrowsValueError(): void
    {
        $this->expectException(ValueError::class);
        Condition::fromArray(['operator' => '=']);
    }

    public function testConditionGroupFromArrayInvalidTypeThrowsValueError(): void
    {
        $this->expectException(ValueError::class);
        ConditionGroup::fromArray([]);
    }
}
