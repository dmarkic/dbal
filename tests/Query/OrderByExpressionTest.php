<?php

namespace Blrf\Tests\Dbal\Query;

use Blrf\Tests\Dbal\TestCase;
use Blrf\Dbal\Query\OrderByExpression;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(OrderByExpression::class)]
class OrderByExpressionTest extends TestCase
{
    public function testConstructWithEmptyExpressionThrowsValueError()
    {
        $this->expectException(\ValueError::class);
        new OrderByExpression('');
    }

    public function testFromArrayAndToArray()
    {
        $a = [
            'expression'    => 'MyExpression',
            'type'          => 'DESC'
        ];
        $e = OrderByExpression::fromArray($a);
        $this->assertSame($a, $e->toArray());
    }

    public function testFromStringAndToStringWithType()
    {
        $s = 'MyExpression DESC';
        $e = OrderByExpression::fromString($s);
        $this->assertSame($s, (string)$e);
    }

    public function testFromStringAndToStringWithoutType()
    {
        $s = 'MyExpression';
        $e = OrderByExpression::fromString($s);
        $this->assertSame($s . ' ASC', (string)$e);
    }
}
