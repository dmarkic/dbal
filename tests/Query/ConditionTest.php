<?php

namespace Blrf\Tests\Dbal\Query;

use Blrf\Tests\Dbal\TestCase;
use Blrf\Dbal\QueryBuilder;
use Blrf\Dbal\Query\Condition;
use Blrf\Dbal\Query\ConditionBuilder;
use Blrf\Dbal\Query\ConditionGroup;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Condition::class)]
#[CoversClass(ConditionBuilder::class)]
#[CoversClass(ConditionGroup::class)]
class ConditionTest extends TestCase
{
    public function testConditionToString()
    {
        $c = new Condition('expr', 'op', 'value');
        $this->assertSame('expr op value', (string)$c);
        $exp = [
            'expression'    => 'expr',
            'operator'      => 'op',
            'value'         => 'value'
        ];
    }

    public function testConditionToAndFromArray()
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

    public function testConditionFromListArray()
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

    public function testConditionGroupToString()
    {
        $c = new ConditionGroup(
            'AND',
            new Condition('a', 'eq', 'b'),
            new Condition('c', 'eq', 'd')
        );
        $this->assertSame('(a eq b AND c eq d)', (string)$c);
    }

    public function testConditionGroupToAndFromArray()
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

    public function testConditionBuilderSimple()
    {
        $b = new ConditionBuilder();
        $c = $b->eq('expr', 'value');
        $this->assertSame('expr = value', (string)$c);
    }

    public function testConditionBuilderGroups()
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
}
