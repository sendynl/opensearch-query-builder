<?php

namespace Sendy\OpenSearchQueryBuilder\Aggregations;

use Sendy\OpenSearchQueryBuilder\AggregationCollection;
use Sendy\OpenSearchQueryBuilder\Aggregations\Concerns\WithAggregations;

class NestedAggregation extends Aggregation
{
    use WithAggregations;

    protected string $path;

    public static function create(
        string $name,
        string $path,
        Aggregation ...$aggregations
    ): self {
        return new self($name, $path, ...$aggregations);
    }

    public function __construct(
        string $name,
        string $path,
        Aggregation ...$aggregations
    ) {
        $this->name = $name;
        $this->path = $path;
        $this->aggregations = new AggregationCollection(...$aggregations);
    }

    public function payload(): array
    {
        return [
            'nested' => [
                'path' => $this->path,
            ],
            'aggs' => $this->aggregations->toArray(),
        ];
    }
}
