<?php

namespace Blrf\Tests\Dbal;

use Blrf\Dbal\Result;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Result::class)]
class ResultTest extends TestCase
{
    public function testCountable()
    {
        $result = new Result();
        $this->assertSame(0, count($result));
    }
}
