<?php

namespace Blrf\Tests\Dbal;

use Blrf\Dbal\ResultStream;
use React\Stream\ReadableStreamInterface;
use React\Stream\WritableStreamInterface;
use React\Stream\ThroughStream;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ResultStream::class)]
class ResultStreamTest extends TestCase
{
    public function testResultStream(): void
    {
        $stream = new ThroughStream();

        $result = new ResultStream($stream);
        $this->assertTrue($result->isReadable());
        $result->pause();
        $result->resume();
        $writeStream = $this->createMock(WritableStreamInterface::class);
        $ret = $result->pipe($writeStream);
        $this->assertSame($writeStream, $ret);
        $result->close();
        $this->assertFalse($result->isReadable());
    }

    public function testOnData(): void
    {
        $stream = new ThroughStream();
        $result = $this->getMockBuilder(ResultStream::class)
            ->setConstructorArgs([$stream])
            ->onlyMethods(['emit'])
            ->getMock();
        $result->expects($this->once())->method('emit')->with('data', [['data']]);
        $result->onData(['data']);
    }

    public function testOnEndNotClosed(): void
    {
        $stream = new ThroughStream();
        $result = $this->getMockBuilder(ResultStream::class)
            ->setConstructorArgs([$stream])
            ->onlyMethods(['emit', 'close'])
            ->getMock();
        $result->expects($this->once())->method('emit')->with('end');
        $result->expects($this->once())->method('close');
        $result->onEnd();
    }

    public function testOnEndClosed(): void
    {
        $stream = new ThroughStream();
        $result = $this->getMockBuilder(ResultStream::class)
            ->setConstructorArgs([$stream])
            ->onlyMethods(['emit'])
            ->getMock();
        $result->close();
        $result->expects($this->never())->method('emit')->with('end');
        $result->onEnd();
    }

    public function testOnError(): void
    {
        $ex = new \Exception('Testing');
        $stream = new ThroughStream();
        $result = $this->getMockBuilder(ResultStream::class)
            ->setConstructorArgs([$stream])
            ->onlyMethods(['emit', 'close'])
            ->getMock();
        $result->expects($this->once())->method('emit')->with('error', [$ex]);
        $result->expects($this->once())->method('close');
        $result->onError($ex);
    }
}
