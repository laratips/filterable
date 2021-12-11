<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine;

use Closure;
use Exception;
use JetBrains\PhpStorm\Pure;
use Laratips\Filterable\RuleEngine\Token\ClosingParenthesis;
use Laratips\Filterable\RuleEngine\Token\DateRange;
use Laratips\Filterable\RuleEngine\Token\Equal;
use Laratips\Filterable\RuleEngine\Token\Like;
use Laratips\Filterable\RuleEngine\Token\OpeningParenthesis;
use Laratips\Filterable\RuleEngine\Token\Token;

class Parser
{
    private ?Token $operator = null;
    private mixed $value = null;

    public function __construct(private Tokenizer $tokenizer, private EloquentBuilderCompiler $compiler)
    {
        //
    }

    /**
     * @throws Exception
     */
    public function parse(string $rule, string $column): Closure
    {
        /** @var Token[] $tokens */
        $tokens = $this->tokenizer->tokenize($rule);

        foreach ($tokens as $token) {
            $handler = $this->getHandlerForType($token->type);
            $handler($token, $this->compiler);

            if ($this->conditionCanBeAdded()) {
                if ($token instanceof DateRange) {
                    $this->addDateConditionFor($column);
                } else {
                    $this->addConditionFor($column);
                }
            }
        }

        return $this->compiler->getCompiledRule();
    }

    private function addDateConditionFor(string $column): void
    {
        $this->compiler->addDateCondition($column, $this->operator->getValue(), $this->value);
        $this->operator = null;
        $this->value = null;
    }

    private function addConditionFor(string $column): void
    {
        $this->compiler->addCondition($column, $this->operator->getValue(), $this->value);
        $this->operator = null;
        $this->value = null;
    }

    private function conditionCanBeAdded(): bool
    {
        return $this->operator !== null && $this->value !== null;
    }

    #[Pure]
    private function getHandlerForType(int $tokenType): callable
    {
        return match ($tokenType) {
            Token::TYPE_OPERATOR => $this->handleOperatorToken(),
            Token::TYPE_VALUE => $this->handleValueToken(),
            Token::TYPE_SPACE => $this->handleDummyToken(),
            Token::TYPE_LOGICAL => $this->handleLogicalToken(),
            Token::TYPE_PARENTHESIS => $this->handleParenthesisToken(),
            default => $this->handleUnknownToken(),
        };
    }

    private function handleParenthesisToken(): Closure
    {
        return static fn (OpeningParenthesis|ClosingParenthesis $token, EloquentBuilderCompiler $compiler) => $compiler->addParenthesis($token);
    }

    private function handleLogicalToken(): Closure
    {
        return static fn (Token $token, EloquentBuilderCompiler $compiler) => $compiler->addLogical($token);
    }

    private function handleValueToken(): Closure
    {
        return function (Token $token) {
            if ($token instanceof DateRange) {
                $this->operator = new Equal('=');
            }

            if ($this->operator === null && $token->getPosition() === 0) {
                $this->operator = new Like('%');
            }

            $this->value = $token->getValue();
        };
    }

    private function handleOperatorToken(): Closure
    {
        return function (Token $token): void {
            if ($this->operator !== null) {
                /** @noinspection ThrowRawExceptionInspection */
                throw new Exception('Unexpected token: ' . $token::class);
            }

            $this->operator = $token;
        };
    }

    private function handleUnknownToken(): Closure
    {
        return static fn (Token $token) => throw new Exception('Unknown token: ' . $token::class);
    }

    private function handleDummyToken(): Closure
    {
        return static function(): void {
            // Do nothing
        };
    }
}
