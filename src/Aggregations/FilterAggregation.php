<?php

namespace Sendy\OpenSearchQueryBuilder\Aggregations;

use Sendy\OpenSearchQueryBuilder\AggregationCollection;
use Sendy\OpenSearchQueryBuilder\Aggregations\Concerns\WithAggregations;
use Sendy\OpenSearchQueryBuilder\Queries\Query;

class FilterAggregation extends Aggregation
{
    use WithAggregations;

    protected Query $filter;

    public static function create(
        string $name,
        Query $filter,
        Aggregation ...$aggregations
    ): self {
        return new self($name, $filter, ...$aggregations);
    }

    public function __construct(
        string $name,
        Query $filter,
        Aggregation ...$aggregations
    ) {
        $this->name = $name;
        $this->filter = $filter;
        $this->aggregations = new AggregationCollection(...$aggregations);
    }

    public function payload(): array
    {
        $aggregation = [
            'filter' => $this->filter->toArray(),
        ];

        if (! $this->aggregations->isEmpty()) {
            $aggregation['aggs'] = $this->aggregations->toArray();
        }

        return $aggregation;
    }
}
