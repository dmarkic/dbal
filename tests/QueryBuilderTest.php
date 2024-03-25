<?php

namespace Blrf\Tests\Dbal;

use Blrf\Dbal\QueryBuilder;
use Blrf\Dbal\Query\Condition;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(QueryBuilder::class)]
class QueryBuilderTest extends TestCase
{
    public function testEmptyQueryBuilder()
    {
        $qb = new QueryBuilder();
        $this->assertSame('SELECT ', $qb->getSql());
        $a = $qb->toArray();
        $exp = [
            'class'         => QueryBuilder::class,
            'type'          => 'SELECT',
            'select'        => [],
            'from'          => [],
            'columns'       => [],
            'where'         => null,
            'orderBy'       => [],
            'limit'         => null,
            'parameters'    => []
        ];
        $this->assertSame($exp, $a);

        $new = QueryBuilder::fromArray($a);
        $this->assertEquals($qb, $new);
    }

    public function testSelect()
    {
        $exp = 'SELECT a,b FROM c WHERE d = ? ORDER BY e ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->where(
                fn($b) => $b->eq('d')
            )
            ->orderBy('e')
            ->limit(1, 2)
            ->setParameters(['f']);
        $this->assertSame(
            $exp,
            $qb->getSql()
        );
        $this->assertSame(['f'], $qb->getParameters());
        $nqb = QueryBuilder::fromArray($qb->toArray());
        $this->assertSame($exp, $nqb->getSql());
        $this->assertSame(['f'], $nqb->getParameters());
    }

    public function testSelectWithAddWhereWithoutPreviousWhere()
    {
        $exp = 'SELECT a,b FROM c WHERE d = ? ORDER BY e ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->andWhere(
                fn($b) => $b->eq('d')
            )
            ->orderBy('e')
            ->limit(1, 2)
            ->setParameters(['f']);
        $this->assertSame(
            $exp,
            $qb->getSql()
        );
        $this->assertSame(['f'], $qb->getParameters());
        $nqb = QueryBuilder::fromArray($qb->toArray());
        $this->assertSame($exp, $nqb->getSql());
        $this->assertSame(['f'], $nqb->getParameters());
    }

    public function testSelectWithAddWhereWithPreviousWhere()
    {
        $exp = 'SELECT a,b FROM c WHERE (d = ? AND g = ?) ORDER BY e ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->andWhere(
                fn($b) => $b->eq('d')
            )
            ->orderBy('e')
            ->limit(1, 2)
            ->setParameters(['f']);
        $qb->andWhere(new Condition('g'));
        $qb->addParameter('h');
        $this->assertSame(
            $exp,
            $qb->getSql()
        );
        $this->assertSame(['f', 'h'], $qb->getParameters());
        $nqb = QueryBuilder::fromArray($qb->toArray());
        $this->assertSame($exp, $nqb->getSql());
        $this->assertSame(['f', 'h'], $nqb->getParameters());
    }

    public function testSelectWithOrWhereWithoutPreviousWhere()
    {
        $exp = 'SELECT a,b FROM c WHERE d = ? ORDER BY e ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->orWhere(
                fn($b) => $b->eq('d')
            )
            ->orderBy('e')
            ->limit(1, 2)
            ->setParameters(['f']);
        $this->assertSame(
            $exp,
            $qb->getSql()
        );
        $this->assertSame(['f'], $qb->getParameters());
        $nqb = QueryBuilder::fromArray($qb->toArray());
        $this->assertSame($exp, $nqb->getSql());
        $this->assertSame(['f'], $nqb->getParameters());
    }

    public function testSelectWithOrWhereWithPreviousWhere()
    {
        $exp = 'SELECT a,b FROM c WHERE (d = ? OR g = ?) ORDER BY e ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->where(
                fn($b) => $b->eq('d')
            )
            ->orderBy('e')
            ->limit(1, 2)
            ->setParameters(['f']);
        $qb->orWhere(new Condition('g'));
        $qb->addParameter('h');
        $this->assertSame(
            $exp,
            $qb->getSql()
        );
        $this->assertSame(['f', 'h'], $qb->getParameters());
        $nqb = QueryBuilder::fromArray($qb->toArray());
        $this->assertSame($exp, $nqb->getSql());
        $this->assertSame(['f', 'h'], $nqb->getParameters());
    }

    public function testUpdate()
    {
        $exp = 'UPDATE a SET b = ?, d = ? WHERE f = ? ORDER BY h ASC LIMIT 1';
        $qb = new QueryBuilder();
        $qb
            ->update('a')
            ->set([
                'b' => 'c',
                'd' => 'e'
            ])->where(
                $qb->condition('f')
            )->addParameter('g')
            ->orderBy('h')
            ->limit(1);
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['c', 'e', 'g'], $qb->getParameters());
        $nqb = QueryBuilder::fromArray($qb->toArray());
        $this->assertSame($exp, $nqb->getSql());
        $this->assertSame(['c', 'e', 'g'], $nqb->getParameters());
    }

    public function testInsert()
    {
        $exp = 'INSERT INTO a (b, d) VALUES(?, ?)';
        $qb = new QueryBuilder();
        $qb
            ->insert('a')
            ->values([
                'b' => 'c',
                'd' => 'f'
            ]);
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['c', 'f'], $qb->getParameters());
        $nqb = QueryBuilder::fromArray($qb->toArray());
        $this->assertSame($exp, $nqb->getSql());
        $this->assertSame(['c', 'f'], $nqb->getParameters());
    }

    public function testDelete()
    {
        $exp = 'DELETE FROM a AS b WHERE (c = ? AND d = ?) ORDER BY e ASC, f DESC LIMIT 5';
        $qb = new QueryBuilder();
        $qb
            ->delete('a', 'b')
            ->where(
                $qb->condition()->and(
                    $qb->condition('c'),
                    $qb->condition('d')
                )
            )
            ->orderBy('e')
            ->orderBy('f', 'DESC')
            ->limit(5);
        $this->assertSame($exp, $qb->getSql());
        $nqb = QueryBuilder::fromArray($qb->toArray());
        $this->assertSame($exp, $nqb->getSql());
    }

    public function testFromArraySelectIsString()
    {
        $data = [
            'select'    => '1'
        ];
        $qb = QueryBuilder::fromArray($data);
        $exp = 'SELECT 1';
        $this->assertSame($exp, $qb->getSql());
    }

    public function testFromArraySelectIsArrayWithString()
    {
        $data = [
            'select'    => ['1']
        ];
        $qb = QueryBuilder::fromArray($data);
        $exp = 'SELECT 1';
        $this->assertSame($exp, $qb->getSql());
    }

    public function testFromArrayFromIsString()
    {
        $data = [
            'select'    => '1',
            'from'      => 'table'
        ];
        $qb = QueryBuilder::fromArray($data);
        $exp = 'SELECT 1 FROM table';
        $this->assertSame($exp, $qb->getSql());
    }

    public function testFromArrayFromIsArrayWithString()
    {
        $data = [
            'select'    => '1',
            'from'      => ['table']
        ];
        $qb = QueryBuilder::fromArray($data);
        $exp = 'SELECT 1 FROM table';
        $this->assertSame($exp, $qb->getSql());
    }

    public function testFromArrayOrderByIsString()
    {
        $data = [
            'select'    => '1',
            'from'      => 'table',
            'orderBy'   => 'column ASC'
        ];
        $qb = QueryBuilder::fromArray($data);
        $exp = 'SELECT 1 FROM table ORDER BY column ASC';
        $this->assertSame($exp, $qb->getSql());
    }

    public function testFromArrayOrderByIsArrayWithString()
    {
        $data = [
            'select'    => '1',
            'from'      => 'table',
            'orderBy'   => ['column ASC']
        ];
        $qb = QueryBuilder::fromArray($data);
        $exp = 'SELECT 1 FROM table ORDER BY column ASC';
        $this->assertSame($exp, $qb->getSql());
    }

    public function testFromArrayLimitDirectlyInData()
    {
        $data = [
            'select'    => '1',
            'limit'     => 1,
            'offset'    => 2
        ];
        $qb = QueryBuilder::fromArray($data);
        $exp = 'SELECT 1 LIMIT 1 OFFSET 2';
        $this->assertSame($exp, $qb->getSql());
    }
}
