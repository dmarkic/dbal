<?php

namespace Blrf\Tests\Dbal;

use Blrf\Dbal\QueryBuilder;
use Blrf\Dbal\Query\Condition;
use Blrf\Dbal\Query\ConditionBuilder;
use Blrf\Dbal\Query\FromExpression;
use Blrf\Dbal\Query\JoinExpression;
use Blrf\Dbal\Query\OrderByExpression;
use Blrf\Dbal\Query\SelectExpression;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(QueryBuilder::class)]
class QueryBuilderTest extends TestCase
{
    public function testEmptyQueryBuilder(): void
    {
        $qb = new QueryBuilder();
        $this->assertSame('SELECT ', $qb->getSql());
        $a = $qb->toArray();
        $exp = [
            'type'          => 'SELECT',
            'select'        => [],
            'from'          => [],
            'join'          => [],
            'columns'       => [],
            'where'         => null,
            'orderBy'       => [],
            'limit'         => null,
            'parameters'    => []
        ];
        $this->assertSame($exp, $a);

        $qb->fromArray($a);
        $this->assertEquals($exp, $qb->toArray());
    }

    public function testSelect(): void
    {
        $exp = 'SELECT a,b FROM c INNER JOIN d AS e ON c.id = e.id WHERE f = ? ORDER BY f ASC, x DESC LIMIT 1 OFFSET 2';

        $expArray = [
            'type'  => 'SELECT',
            'select' => [
                [
                    'expression'    => 'a',
                    'alias'         => null
                ],
                [
                    'expression'    => 'b',
                    'alias'         => null
                ]
            ],
            'from' => [
                [
                    'expression'    => 'c',
                    'alias'         => null
                ]
            ],
            'join'  => [
                [
                    'type'  => 'INNER',
                    'table' => 'd',
                    'on'    => 'c.id = e.id',
                    'alias' => 'e'
                ]
            ],
            'columns'   => [],
            'where'     => [
                'expression'    => 'f',
                'operator'      => '=',
                'value'         => '?'
            ],
            'orderBy'   => [
                [
                    'expression'    => 'f',
                    'type'          => 'ASC'
                ],
                [
                    'expression'    => 'x',
                    'type'          => 'DESC'
                ]
            ],
            'limit' => [
                'limit'     => 1,
                'offset'    => 2
            ],
            'parameters'    => ['h']
        ];
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->join('d', 'c.id = e.id', 'e')
            ->where(
                fn(ConditionBuilder $b) => $b->eq('f')
            )
            ->orderBy('f')
            ->orderBy(new OrderByExpression('x', 'DESC'))
            ->limit(1, 2)
            ->setParameters(['h']);
        $this->assertSame(
            $exp,
            $qb->getSql()
        );
        $a = $qb->toArray();
        $this->assertSame($expArray, $a);
        $this->assertSame(['h'], $qb->getParameters());
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['h'], $qb->getParameters());
        $this->assertSame($expArray, $qb->toArray());
    }

    public function testSelectLeftJoin(): void
    {
        $exp = 'SELECT a,b FROM c LEFT JOIN d AS e ON c.id = e.id WHERE f = ? ORDER BY f ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->leftJoin('d', 'c.id = e.id', 'e')
            ->where(
                fn(ConditionBuilder $b) => $b->eq('f')
            )
            ->orderBy('f')
            ->limit(1, 2)
            ->setParameters(['h']);
        $this->assertSame(
            $exp,
            $qb->getSql()
        );
        $this->assertSame(['h'], $qb->getParameters());
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['h'], $qb->getParameters());
    }

    public function testSelectRightJoinWithoutAlias(): void
    {
        $exp = 'SELECT a,b FROM c RIGHT JOIN d ON c.id = d.id WHERE f = ? ORDER BY f ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->rightJoin('d', 'c.id = d.id')
            ->where(
                fn(ConditionBuilder $b) => $b->eq('f')
            )
            ->orderBy('f')
            ->limit(1, 2)
            ->setParameters(['h']);
        $this->assertSame(
            $exp,
            $qb->getSql()
        );
        $this->assertSame(['h'], $qb->getParameters());
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['h'], $qb->getParameters());
    }

    public function testSelectFullJoin(): void
    {
        $exp = 'SELECT a,b FROM c FULL JOIN d AS e ON c.id = e.id WHERE f = ? ORDER BY f ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->fullJoin('d', 'c.id = e.id', 'e')
            ->where(
                fn(ConditionBuilder $b) => $b->eq('f')
            )
            ->orderBy('f')
            ->limit(1, 2)
            ->setParameters(['h']);
        $this->assertSame(
            $exp,
            $qb->getSql()
        );
        $this->assertSame(['h'], $qb->getParameters());
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['h'], $qb->getParameters());
    }

    public function testSelectWithExpression(): void
    {
        $qb = new QueryBuilder();
        $qb->select(new SelectExpression('a', 'b'));
        $qb->from(new FromExpression('c', 'd'));
        $exp = 'SELECT a AS b FROM c AS d';
        $this->assertSame($exp, $qb->getSql());
    }

    public function testSelectWithAddWhereWithoutPreviousWhere(): void
    {
        $exp = 'SELECT a,b FROM c WHERE d = ? ORDER BY e ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->andWhere(
                fn(ConditionBuilder $b) => $b->eq('d')
            )
            ->orderBy('e')
            ->limit(1, 2)
            ->setParameters(['f']);
        $this->assertSame(
            $exp,
            $qb->getSql()
        );
        $this->assertSame(['f'], $qb->getParameters());
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['f'], $qb->getParameters());
    }

    public function testSelectWithAddWhereWithPreviousWhere(): void
    {
        $exp = 'SELECT a,b FROM c WHERE (d = ? AND g = ?) ORDER BY e ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->andWhere(
                fn(ConditionBuilder $b) => $b->eq('d')
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
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['f', 'h'], $qb->getParameters());
    }

    public function testSelectWithOrWhereWithoutPreviousWhere(): void
    {
        $exp = 'SELECT a,b FROM c WHERE d = ? ORDER BY e ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->orWhere(
                fn(ConditionBuilder $b) => $b->eq('d')
            )
            ->orderBy('e')
            ->limit(1, 2)
            ->setParameters(['f']);
        $this->assertSame(
            $exp,
            $qb->getSql()
        );
        $this->assertSame(['f'], $qb->getParameters());
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['f'], $qb->getParameters());
    }

    public function testSelectWithOrWhereWithPreviousWhere(): void
    {
        $exp = 'SELECT a,b FROM c WHERE (d = ? OR g = ?) ORDER BY e ASC LIMIT 1 OFFSET 2';
        $qb = new QueryBuilder();
        $qb
            ->select('a', 'b')
            ->from('c')
            ->where(
                fn(ConditionBuilder $b) => $b->eq('d')
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
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['f', 'h'], $qb->getParameters());
    }

    public function testUpdate(): void
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
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['c', 'e', 'g'], $qb->getParameters());
    }

    public function testInsert(): void
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
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['c', 'f'], $qb->getParameters());
    }

    public function testInsertWithFromExpression(): void
    {
        $exp = 'INSERT INTO a (b, d) VALUES(?, ?)';
        $qb = new QueryBuilder();
        $qb
            ->insert(new FromExpression('a'))
            ->values([
                'b' => 'c',
                'd' => 'f'
            ]);
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['c', 'f'], $qb->getParameters());
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
        $this->assertSame(['c', 'f'], $qb->getParameters());
    }

    public function testDelete(): void
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
        $qb = $qb->fromArray($qb->toArray());
        $this->assertSame($exp, $qb->getSql());
    }

    public function testFromArrayWithoutType(): void
    {
        $this->expectException(\ValueError::class);
        $data = [
            'select'    => '1'
        ];
        // @phpstan-ignore-next-line
        $qb = (new QueryBuilder())->fromArray($data);
    }

    public function testFromArrayFromIsQueryBuilderArray(): void
    {
        $data = [
            'type'  => 'SELECT',
            'from'  => [
                [
                    'expression'    => [
                        'type'  => 'SELECT',
                        'select'    => [
                            ['expression'   => '2']
                        ]
                    ],
                    'alias' => 'alias'
                ]
            ]
        ];
        $qb = (new QueryBuilder())->fromArray($data);
        $this->assertSame('SELECT  FROM (SELECT 2) AS alias', $qb->getSql());
    }

    public function testFromArrayWithExpressionObjects(): void
    {
        $exp = 'SELECT 1 AS number FROM table AS alias INNER JOIN table AS jalias ON on ' .
               'ORDER BY number DESC LIMIT 1 OFFSET 2';
        $data = [
            'type'  => 'SELECT',
            'select' => [new SelectExpression('1', 'number')],
            'from'      => [new FromExpression('table', 'alias')],
            'join'      => [new JoinExpression('INNER', 'table', 'on', 'jalias')],
            'orderBy'   => [new OrderByExpression('number', 'DESC')],
            'limit'     => 1,
            'offset'    => 2
        ];
        $qb = (new QueryBuilder())->fromArray($data);
        $this->assertSame($exp, $qb->getSql());
    }

    public function testQuoteIdentifierSingle(): void
    {
        $id = 'foobar';
        $qb = new QueryBuilder();
        $this->assertSame('`foobar`', $qb->quoteIdentifier($id));
    }

    public function testQuoteIdentifiers(): void
    {
        $id = 'foo.bar.id';
        $qb = new QueryBuilder();
        $this->assertSame('`foo`.`bar`.`id`', $qb->quoteIdentifier($id));
    }
}
