<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Laratips\Filterable\RuleEngine\Token\ClosingParenthesis;
use Laratips\Filterable\RuleEngine\Token\LogicalAnd;
use Laratips\Filterable\RuleEngine\Token\LogicalOr;
use Laratips\Filterable\RuleEngine\Token\OpeningParenthesis;
use Laratips\Filterable\RuleEngine\Token\Token;
use Laratips\Filterable\RuleEngine\Token\ValueToken;

use function count;
use function date;

class EloquentBuilderCompiler
{
    private ?Token $lastToken = null;
    private int $openParenthesis = 0;
    private int $closedParenthesis = 0;

    private string $lastBoolean = 'and';
    private array $conditions = [];

    public function __construct()
    {
        //
    }

    /**
     * @throws Exception
     * @noinspection ThrowRawExceptionInspection
     */
    public function getCompiledRule(): Closure
    {
        if ($this->isIncompleteCondition()) {
            throw new Exception('Incomplete condition');
        }

        if (!$this->parenthesisMatch()) {
            throw new Exception('Missing closing parenthesis');
        }

        return $this->mergeQueries();
    }

    private function mergeQueries(): Closure
    {
        return function (Builder $query) {
            foreach ($this->conditions as [$column, $operator, $value, $boolean]) {
                if ($operator === 'between') {
                    $query->whereBetween($column, $value, $boolean);
                } else {
                    $query->where($column, $operator, $value, $boolean);
                }
            }
        };
    }

    public function addCondition(string $column, string $operator, mixed $value): void
    {
        if ($operator === 'like') {
            $value = '%' . $value . '%';
        }
        $this->conditions[] = [$column, $operator, $value, $this->lastBoolean];
        $this->lastToken = new ValueToken($value);
    }

    public function addDateCondition(string $column, string $operator, mixed $value): void
    {
        if (count($value) > 0 && $value[0] === $value[1]) {
            $value = date('Y-m-d', $value[0]);
        } else {
            $operator = 'between';
            $value = [date('Y-m-d H:i:s', $value[0]), date('Y-m-d H:i:s', $value[1])];
        }

        $this->conditions[] = [new Expression("DATE(`$column`)"), $operator, $value, $this->lastBoolean];
        $this->lastToken = new ValueToken($value);
    }

    private function parenthesisMatch(): bool
    {
        return $this->openParenthesis === $this->closedParenthesis;
    }

    private function isIncompleteCondition(): bool
    {
        return $this->lastToken instanceof LogicalAnd || $this->lastToken instanceof LogicalOr;
    }

    /**
     * @throws Exception
     */
    public function addParenthesis(OpeningParenthesis|ClosingParenthesis $token): void
    {
        if ($token instanceof OpeningParenthesis) {
            if (!$this->expectOpeningParenthesis()) {
                /** @noinspection ThrowRawExceptionInspection */
                throw new Exception('Unexpected token: ' . $token::class);
            }
            $this->openParenthesis++;
        } else {
            $this->closedParenthesis++;
        }

        $this->lastToken = $token;
    }

    private function expectOpeningParenthesis(): bool
    {
        return $this->lastToken === null || $this->lastToken instanceof LogicalAnd
            || $this->lastToken instanceof LogicalOr || $this->lastToken instanceof OpeningParenthesis;
    }

    /**
     * @throws Exception
     */
    public function addLogical(Token $token): void
    {
        if ($this->lastToken instanceof LogicalAnd || $this->lastToken instanceof LogicalOr) {
            /** @noinspection ThrowRawExceptionInspection */
            throw new Exception('Unexpected token: ' . $token::class);
        }

        $this->lastToken = $token;
        $this->lastBoolean = $token instanceof LogicalAnd ? 'and' : 'or';
    }

}
