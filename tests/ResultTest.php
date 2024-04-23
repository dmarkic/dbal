<?php

namespace Blrf\Tests\Dbal;

use Blrf\Dbal\Result;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Result::class)]
class ResultTest extends TestCase
{
    public function testCountable(): void
    {
        $result = new Result();
        $this->assertSame(0, count($result));
    }

    public function testIterator(): void
    {
        $result = new Result([['column' => 'value']]);
        $this->assertSame(1, count($result));
        foreach ($result as $row) {
            $this->assertSame(
                [
                    'column'    => 'value'
                ],
                $row
            );
        }
        $this->assertSame(1, $result->key());
        $result->rewind();
        $this->assertSame(0, $result->key());
    }
}
