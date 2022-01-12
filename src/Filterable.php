<?php

declare(strict_types=1);

namespace Laratips\Filterable;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Schema;
use Laratips\Filterable\RuleEngine\EloquentBuilderCompiler;
use Laratips\Filterable\RuleEngine\FilterGrammar;
use Laratips\Filterable\RuleEngine\Parser;
use Laratips\Filterable\RuleEngine\Tokenizer;

use Psr\Container\ContainerExceptionInterface;

use Psr\Container\NotFoundExceptionInterface;

use function array_filter;
use function count;
use function in_array;
use function is_array;

use function strrpos;

use function substr;

use const ARRAY_FILTER_USE_KEY;

/**
 * Data filtering trait
 *
 * @method static Builder filterable(array $exclude = []) apply filtering behavior to the model
 */
trait Filterable
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function scopeFilterable(Builder $query, array $exclude = [], array $filters = []): Builder
    {
        if (count($filters) > 0) {
            $params = $filters;
        } else {
            /** @var Request $request */
            $request = Container::getInstance()->get(Request::class);
            $params = $request->get($this->getFilterQueryParameterName());
        }

        if (is_array($params)) {
            $params = array_filter(
                $params,
                static fn(string $key) => !in_array($key, $exclude, true),
                ARRAY_FILTER_USE_KEY
            );

            return $this->queryWhereBuilder($query, $params);
        }

        return $query;
    }

    /**
     * @throws Exception
     */
    private function queryWhereBuilder(Builder $query, array $params): Builder
    {
        // $model = $query->getModel();
        $tokenizer = new Tokenizer(new FilterGrammar());
        // TODO: move column logic to compiler or parser or maybe other way
        // to implement having logic for aliased columns (aggregates)

        foreach ($params as $column => $rule) {
            $parser = new Parser($tokenizer, new EloquentBuilderCompiler());
            $position = strrpos($column, '.');
            if ($position !== false) {
                $relation = substr($column, 0, $position);
                $column = substr($column, $position + 1);
                $callback = $parser->parse($rule, $column);
                $query->whereHas($relation, $callback);
            } else {
                $callback = $parser->parse($rule, $column);
                $query = $query->where($callback);
            }
        }

        return $query;
    }

    /**
     * Checks if column exists on model, checking sortable property and falling back to DB check
     */
    private function columnExists(Model $model, string $column): bool
    {
        return isset($model->filterable) ? in_array($column, $model->filterable, true)
            : Schema::connection($model->getConnectionName())->hasColumn($model->getTable(), $column);
    }

    /**
     * Returns filter query parameter name from config
     */
    private function getFilterQueryParameterName(): string
    {
        // TODO: read parameter name from configuration
        return 'filter';
    }
}
