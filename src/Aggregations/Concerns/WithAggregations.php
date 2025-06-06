<?php

namespace Sendy\OpenSearchQueryBuilder\Aggregations\Concerns;

use Sendy\OpenSearchQueryBuilder\AggregationCollection;
use Sendy\OpenSearchQueryBuilder\Aggregations\Aggregation;

trait WithAggregations
{
    protected AggregationCollection $aggregations;

    public function aggregation(Aggregation $aggregation): self
    {
        $this->aggregations->add($aggregation);

        return $this;
    }
}
