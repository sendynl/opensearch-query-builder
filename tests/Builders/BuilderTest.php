<?php

namespace Sendy\OpenSearchQueryBuilder\Tests\Builders;

use OpenSearch\Client;
use OpenSearch\TransportFactory;
use PHPUnit\Framework\TestCase;
use Sendy\OpenSearchQueryBuilder\Builder;
use Sendy\OpenSearchQueryBuilder\Queries\NestedQuery\InnerHits;
use Sendy\OpenSearchQueryBuilder\Sorts\Sort;

class BuilderTest extends TestCase
{
    protected Client $client;

    public function setUp(): void
    {
        $transport = (new TransportFactory())
            ->setHttpClient(new \Http\Mock\Client())
            ->create();

        $this->client = new Client($transport);
    }

    public function testGeneratesCollapseWithPlainArrayData(): void
    {
        $innerHits = [
            'name' => 'first_group',
            'size' => 1,
            'sort' => [
                [ 'name.keyword' => [ 'order' => 'asc' ] ],
            ],
        ];

        $builder = (new Builder($this->client))
            ->collapse('group_id', $innerHits);

        self::assertEquals(
            [ 'collapse' => [ 'field' => 'group_id', 'inner_hits' => $innerHits ] ],
            $builder->getPayload()
        );
    }

    public function testGeneratesCollapseWithInnerHitsObject(): void
    {
        $innerHits = InnerHits::create('first_group')
            ->size(1)
            ->addSort(new Sort('name.keyword', 'asc'));

        $builder = (new Builder($this->client))
            ->collapse('group_id', $innerHits);

        self::assertEquals(
            [
                'collapse' => [
                    'field' => 'group_id', 'inner_hits' => [
                        'name' => 'first_group',
                        'size' => 1,
                        'sort' => [
                            [
                                'name.keyword' => [
                                    'order' => 'asc',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            $builder->getPayload()
        );
    }

    public function testMinScoreIsAppliedToThePayload(): void
    {
        $payload = (new Builder($this->client))
            ->minScore(0.1)
            ->getPayload();

        $this->assertArrayHasKey('min_score', $payload);
        $this->assertEquals(0.1, $payload['min_score']);
    }
}
