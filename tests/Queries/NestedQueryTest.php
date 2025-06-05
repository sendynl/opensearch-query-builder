<?php

namespace Sendy\OpenSearchQueryBuilder\Tests\Queries;

use PHPUnit\Framework\TestCase;
use Sendy\OpenSearchQueryBuilder\Queries\NestedQuery;
use Sendy\OpenSearchQueryBuilder\Queries\NestedQuery\InnerHits;
use Sendy\OpenSearchQueryBuilder\Queries\Query;

class NestedQueryTest extends TestCase
{
    private NestedQuery $nestedQuery;

    protected function setUp(): void
    {
        $queryMock = $this->createMock(Query::class);

        $queryMock
            ->method('toArray')
            ->willReturn(['query']);

        $this->nestedQuery = new NestedQuery('path', $queryMock);
    }

    public function testToArrayBuildsCorrectNestedQuery(): void
    {
        $this->assertEquals(
            [
                'nested' => [
                    'path' => 'path',
                    'query' => ['query'],
                ],
            ],
            $this->nestedQuery->toArray()
        );
    }

    public function testToArrayBuildsCorrectNestedQueryWithScoreMode(): void
    {
        $this->assertEquals(
            [
                'nested' => [
                    'path' => 'path',
                    'query' => ['query'],
                    'score_mode' => NestedQuery::SCORE_MODE_MIN,
                ],
            ],
            $this->nestedQuery->scoreMode(NestedQuery::SCORE_MODE_MIN)->toArray()
        );
    }

    public function testToArrayBuildsCorrectNestedQueryWithIgnoreUnmapped(): void
    {
        $this->assertEquals(
            [
                'nested' => [
                    'path' => 'path',
                    'query' => ['query'],
                    'ignore_unmapped' => true,
                ],
            ],
            $this->nestedQuery->ignoreUnmapped(true)->toArray()
        );
    }

    public function testToArrayBuildsCorrectNestedQueryWithInnerHits(): void
    {
        $innerHitsMock = $this->createMock(InnerHits::class);
        $innerHitsMock
            ->method('toArray')
            ->willReturn(
                [
                    'size' => 10,
                    'name' => 'test',
                ]
            );

        $this->assertEquals(
            [
                'nested' => [
                    'path' => 'path',
                    'query' => ['query'],
                    'inner_hits' => [
                        'size' => 10,
                        'name' => 'test',
                    ],
                ],
            ],
            $this->nestedQuery->innerHits($innerHitsMock)->toArray()
        );
    }
}
