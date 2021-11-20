<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine\Token;

abstract class Token
{
    public const TYPE_OPERATOR = 1;
    public const TYPE_VALUE = 2;
    public const TYPE_LOGICAL = 8;
    public const TYPE_VARIABLE = 16;
    public const TYPE_SPACE = 32;
    public const TYPE_PARENTHESIS = 64;

    public function __construct(public int $type, private mixed $value, private ?int $cursor = null)
    {
        //
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function isValue(): bool
    {
        return $this->type === static::TYPE_VALUE;
    }

    public function getPosition(): ?int
    {
        return $this->cursor;
    }

    public function isWhitespace(): bool
    {
        return $this->type === static::TYPE_SPACE;
    }

    public function isOperator(): bool
    {
        return $this->type === static::TYPE_OPERATOR;
    }

    public function isLogical(): bool
    {
        return $this->type === static::TYPE_LOGICAL;
    }

    public function isParenthesis(): bool
    {
        return $this->type === static::TYPE_PARENTHESIS;
    }
}
