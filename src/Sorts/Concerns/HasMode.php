<?php

namespace Sendy\OpenSearchQueryBuilder\Sorts\Concerns;

trait HasMode
{
    protected ?string $mode = null;

    public function mode(string $mode): static
    {
        $this->mode = $mode;

        return $this;
    }
}
