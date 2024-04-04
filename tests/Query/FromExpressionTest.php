<?php

namespace Blrf\Tests\Dbal\Query;

use Blrf\Tests\Dbal\TestCase;
use Blrf\Dbal\QueryBuilder;
use Blrf\Dbal\Query\FromExpression;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FromExpression::class)]
class FromExpressionTest extends TestCase
{
    public function testConstructWithEmptyExpressionThrowsValueError(): void
    {
        $this->expectException(\ValueError::class);
        new FromExpression('');
    }

    public function testFromArrayAndToArraySimple(): void
    {
        $a = ['expression' => 'MyExpression', 'alias' => 'MyAlias'];
        $e = FromExpression::fromArray($a);
        $this->assertSame($a, $e->toArray());
    }

    public function testFromArrayAndToArraySubquery(): void
    {
        $a = [
            'expression'    => (new QueryBuilder())->toArray(),
            'alias'         => 'myAlias'
        ];
        $e = FromExpression::fromArray($a);
        $this->assertSame($a, $e->toArray());
    }

    public function testFromStringAndToStringWithAlias(): void
    {
        $s = 'from AS alias';
        $e = FromExpression::fromString($s);
        $this->assertSame($s, (string)$e);
    }

    public function testFromStringAndToStringWithoutAlias(): void
    {
        $s = 'from';
        $e = FromExpression::fromString($s);
        $this->assertSame($s, (string)$e);
    }

    public function testToStringWithSubquery(): void
    {
        $e = new FromExpression(new QueryBuilder(), 't1');
        $this->assertSame('(SELECT ) AS t1', (string)$e);
    }
}
