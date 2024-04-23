<?php

namespace Blrf\Tests\Dbal\Query;

use Blrf\Tests\Dbal\TestCase;
use Blrf\Dbal\Query\JoinExpression;
use Blrf\Dbal\Query\JoinType;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(JoinExpression::class)]
class JoinExpressionTest extends TestCase
{
    public function testConstructWithEmptyTableThrowsValueError(): void
    {
        $this->expectException(\ValueError::class);
        new JoinExpression(JoinType::INNER, '', '');
    }

    public function testConstructWithEmptyOnThrowsValueError(): void
    {
        $this->expectException(\ValueError::class);
        new JoinExpression(JoinType::INNER, 'table', '');
    }

    public function testConstructDefaultTypeIsInner(): void
    {
        $expr = new JoinExpression('', 'table', 'on');
        $this->assertSame($expr->type, JoinType::INNER);
    }

    public function testToStringWithoutAlias(): void
    {
        $expr = new JoinExpression(JoinType::LEFT, 'table', 'on');
        $this->assertSame('LEFT JOIN table ON on', $expr->__toString());
    }

    public function testToStringWithAlias(): void
    {
        $expr = new JoinExpression(JoinType::LEFT, 'table', 'on', 'alias');
        $this->assertSame('LEFT JOIN table AS alias ON on', $expr->__toString());
    }

    public function testToArray(): void
    {
        $expr = new JoinExpression(JoinType::FULL, 'table', 'on', 'alias');
        $exp = [
            'type'  => JoinType::FULL->value,
            'table' => 'table',
            'on'    => 'on',
            'alias' => 'alias'
        ];
        $this->assertSame($exp, $expr->toArray());
    }
}
