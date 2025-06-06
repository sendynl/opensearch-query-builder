<?php

namespace Sendy\OpenSearchQueryBuilder\Aggregations;

use Sendy\OpenSearchQueryBuilder\Sorts\Sort;

class TopHitsAggregation extends Aggregation
{
    protected int $size;

    protected ?Sort $sort = null;

    public static function create(string $name, int $size, ?Sort $sort = null): TopHitsAggregation
    {
        return new self($name, $size, $sort);
    }

    public function __construct(
        string $name,
        int $size,
        ?Sort $sort = null
    ) {
        $this->name = $name;
        $this->size = $size;
        $this->sort = $sort;
    }

    public function payload(): array
    {
        $parameters = [
            'size' => $this->size,
        ];

        if ($this->sort) {
            $parameters['sort'] = [$this->sort->toArray()];
        }

        return [
            'top_hits' => $parameters,
        ];
    }
}
