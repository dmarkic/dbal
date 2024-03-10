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
    }
}
