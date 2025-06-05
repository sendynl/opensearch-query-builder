<?php

namespace Sendy\OpenSearchQueryBuilder\Tests\Queries;

use OpenSearch\Client;
use OpenSearch\TransportFactory;
use PHPUnit\Framework\TestCase;
use Sendy\OpenSearchQueryBuilder\Builder;

class CollapseTest extends TestCase
{

    private Builder $builder;

    private Client $client;

    protected function setUp(): void
    {
        $transport = (new TransportFactory())
            ->setHttpClient(new \Http\Mock\Client())
            ->create();

        $this->client = new Client($transport);

        $this->builder = new Builder($this->client);
    }

    public function testCollapseIsAddedToPayload()
    {
        $this->builder->collapse(
            'user_id',
            [
                'name' => 'top_comments',
                'size' => 3,
                'sort' => [
                    [
                        'timestamp' => 'desc',
                    ],
                ],
            ],
            10,
        );

        $payload = $this->builder->getPayload();

        $expectedCollapse = [
            'field' => 'user_id',
            'inner_hits' => [
                'name' => 'top_comments',
                'size' => 3,
                'sort' => [
                    [
                        'timestamp' => 'desc',
                    ],
                ],
            ],
            'max_concurrent_group_searches' => 10,
        ];

        $this->assertArrayHasKey('collapse', $payload);
        $this->assertEquals($expectedCollapse, $payload['collapse']);
    }
}
