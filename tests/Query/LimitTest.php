<?php

namespace Blrf\Tests\Dbal\Query;

use Blrf\Tests\Dbal\TestCase;
use Blrf\Dbal\Query\Limit;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Limit::class)]
class LimitTest extends TestCase
{
    public function testConstructWithNoArguments()
    {
        $this->expectException(\ValueError::class);
        new Limit(null);
    }

    public function testFromArrayAndToArray()
    {
        $a = ['limit' => 10, 'offset' => 11];
        $l = Limit::fromArray($a);
        $this->assertSame($a, $l->toArray());
    }

    public function testFromStringAndToStringWithOffset()
    {
        $s = 'LIMIT 10 OFFSET 20';
        $l = Limit::fromString($s);
        $this->assertSame($s, (string)$l);
    }

    public function testFromStringAndToStringWithoutOffset()
    {
        $s = 'LIMIT 10';
        $l = Limit::fromString($s);
        $this->assertSame($s, (string)$l);
    }
}
