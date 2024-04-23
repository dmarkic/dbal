<?php

namespace Blrf\Tests\Dbal\Query;

use Blrf\Tests\Dbal\TestCase;
use Blrf\Dbal\Query\Limit;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Limit::class)]
class LimitTest extends TestCase
{
    public function testConstructWithNoArguments(): void
    {
        $this->expectException(\ValueError::class);
        new Limit(null);
    }

    public function testWithLimitAndOffset(): void
    {
        $l = new Limit(1, 2);
        $this->assertSame('LIMIT 1 OFFSET 2', (string)$l);
        $exp = [
            'limit'     => 1,
            'offset'    => 2
        ];
        $this->assertSame($exp, $l->toArray());
    }
}
