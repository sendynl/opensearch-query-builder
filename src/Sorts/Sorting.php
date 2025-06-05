<?php

namespace Sendy\OpenSearchQueryBuilder\Sorts;

interface Sorting
{
    public const ASC = 'asc';
    public const DESC = 'desc';

    public function toArray(): array;
}
