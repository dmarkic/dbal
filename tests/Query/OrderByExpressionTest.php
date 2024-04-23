<?php

namespace Blrf\Tests\Dbal\Query;

use Blrf\Tests\Dbal\TestCase;
use Blrf\Dbal\Query\OrderByExpression;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(OrderByExpression::class)]
class OrderByExpressionTest extends TestCase
{
    public function testConstructWithEmptyExpressionThrowsValueError(): void
    {
        $this->expectException(\ValueError::class);
        new OrderByExpression('');
    }

    public function testWithStringType(): void
    {
        $o = new OrderByExpression('expr', 'asc');
        $this->assertSame('expr ASC', (string)$o);
        $exp = [
            'expression'    => 'expr',
            'type'          => 'ASC'
        ];
        $this->assertSame($exp, $o->toArray());
    }
}
