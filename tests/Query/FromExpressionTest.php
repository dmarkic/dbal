<?php

namespace Blrf\Tests\Dbal\Query;

use Blrf\Tests\Dbal\TestCase;
use Blrf\Dbal\QueryBuilder;
use Blrf\Dbal\Query\FromExpression;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FromExpression::class)]
class FromExpressionTest extends TestCase
{
    public function testConstructWithEmptyExpressionThrowsValueError()
    {
        $this->expectException(\ValueError::class);
        new FromExpression('');
    }

    public function testFromArrayAndToArraySimple()
    {
        $a = ['expression' => 'MyExpression', 'alias' => 'MyAlias'];
        $e = FromExpression::fromArray($a);
        $this->assertSame($a, $e->toArray());
    }

    public function testFromArrayAndToArraySubquery()
    {
        $a = [
            'expression'    => (new QueryBuilder())->toArray(),
            'alias'         => 'myAlias'
        ];
        $e = FromExpression::fromArray($a);
        $this->assertSame($a, $e->toArray());
    }

    public function testFromStringAndToStringWithAlias()
    {
        $s = 'from AS alias';
        $e = FromExpression::fromString($s);
        $this->assertSame($s, (string)$e);
    }

    public function testFromStringAndToStringWithoutAlias()
    {
        $s = 'from';
        $e = FromExpression::fromString($s);
        $this->assertSame($s, (string)$e);
    }

    public function testToStringWithSubquery()
    {
        $e = new FromExpression(new QueryBuilder(), 't1');
        $this->assertSame('(SELECT ) AS t1', (string)$e);
    }
}
