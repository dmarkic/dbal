<?php

namespace Blrf\Tests\Dbal\Query;

use Blrf\Tests\Dbal\TestCase;
use Blrf\Dbal\Query\SelectExpression;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SelectExpression::class)]
class SelectExpressionTest extends TestCase
{
    public function testConstructWithEmptyExpressionThrowsValueError(): void
    {
        $this->expectException(\ValueError::class);
        new SelectExpression('');
    }

    public function testFromArrayAndToArray(): void
    {
        $a = [
            'expression'    => 'MyExpression',
            'alias'         => 'MyAlias'
        ];
        $e = SelectExpression::fromArray($a);
        $this->assertSame($a, $e->toArray());
    }

    public function testFromStringAndToStringWithAlias(): void
    {
        $s = 'MyExpression AS MyAlias';
        $e = SelectExpression::fromString($s);
        $this->assertSame($s, (string)$e);
    }

    public function testFromStringAndToStringWithoutAlias(): void
    {
        $s = '1+1';
        $e = SelectExpression::fromString($s);
        $this->assertSame($s, (string)$e);
    }
}
