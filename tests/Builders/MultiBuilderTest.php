<?php

namespace Sendy\OpenSearchQueryBuilder\Tests\Builders;

use Elastic\Elasticsearch\Client;
use Elastic\Transport\TransportBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sendy\OpenSearchQueryBuilder\Builder;
use Sendy\OpenSearchQueryBuilder\MultiBuilder;
use Sendy\OpenSearchQueryBuilder\Queries\TermQuery;

class MultiBuilderTest extends TestCase
{
    private MultiBuilder $multiBuilder;

    private Client $client;

    protected function setUp(): void
    {
        $transport = TransportBuilder::create()
            ->setClient(new \Http\Mock\Client())
            ->build();

        $logger = $this->createStub(LoggerInterface::class);

        $this->client = new Client($transport, $logger);

        $this->multiBuilder = new MultiBuilder($this->client);
    }

    public function testEmptyPayloadGeneratesCorrectly(): void
    {
        $this->assertEmpty($this->multiBuilder->getPayload());
    }

    public function testSingleBuilderPayloadGeneratesCorrectly(): void
    {
        $this->multiBuilder->addBuilder(
            (new Builder($this->client))->addQuery(TermQuery::create('test', 'value'))
        );

        $payload = $this->multiBuilder->getPayload();

        $this->assertNotEmpty($payload);

        $this->assertCount(2, $payload);

        $this->assertEquals([], $payload[0]);

        $this->assertEquals([
            'query' => [
                'bool' => [
                    'must' => [
                        ['term' => ['test' => 'value']],
                    ],
                ],
            ],
        ], $payload[1]);
    }

    public function testMultipleBuilderPayloadGeneratesCorrectly(): void
    {
        $this->multiBuilder->addBuilder(
            (new Builder($this->client))
                ->index('firstIndex')
                ->addQuery(TermQuery::create('keyword', 'value'), 'filter'),
        );
        $this->multiBuilder->addBuilder(
            (new Builder($this->client))
                ->addQuery(TermQuery::create('keyword', 'value'), 'filter'),
            'secondIndex'
        );

        $payload = $this->multiBuilder->getPayload();

        $this->assertNotEmpty($payload);
        $this->assertCount(4, $payload);

        $index = $payload[0];
        $this->assertEquals(['index' => 'firstIndex'], $index);

        $body = $payload[1];
        $this->assertEquals([
            'query' => [
                'bool' => [
                    'filter' => [['term' => ['keyword' => 'value']]],
                ],
            ],
        ], $body);

        $index = $payload[2];
        $this->assertEquals(['index' => 'secondIndex'], $index);

        $body = $payload[3];
        $this->assertEquals([
            'query' => [
                'bool' => [
                    'filter' => [['term' => ['keyword' => 'value']]],
                ],
            ],
        ], $body);
    }
}
