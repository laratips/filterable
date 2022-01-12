<?php

declare(strict_types=1);

namespace Laratips\Filterable;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FilteringScope implements Scope
{
    use Filterable;

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function apply(Builder $builder, Model $model, array $exclude = [], array $filters = []): void
    {
        $this->scopeFilterable($builder, $exclude, $filters);
    }
}
