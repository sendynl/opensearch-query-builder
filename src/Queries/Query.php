<?php

namespace Sendy\OpenSearchQueryBuilder\Queries;

interface Query
{
    public function toArray(): array;
}
