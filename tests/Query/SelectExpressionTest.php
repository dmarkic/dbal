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

    public function testSelectWithoutAlias(): void
    {
        $s = new SelectExpression('expr');
        $this->assertSame('expr', (string)$s);
        $exp = [
            'expression'    => 'expr',
            'alias'         => null
        ];
        $this->assertSame($exp, $s->toArray());
    }

    public function testSelectWithAlias(): void
    {
        $s = new SelectExpression('expr', 'alias');
        $this->assertSame('expr AS alias', (string)$s);
        $exp = [
            'expression'    => 'expr',
            'alias'         => 'alias'
        ];
        $this->assertSame($exp, $s->toArray());
    }
}
