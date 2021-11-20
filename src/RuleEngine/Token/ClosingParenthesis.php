<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine\Token;

use JetBrains\PhpStorm\Pure;

class ClosingParenthesis extends Token
{
    #[Pure]
    public function __construct(public mixed $value, public ?int $cursor = null)
    {
        parent::__construct(Token::TYPE_PARENTHESIS, $this->value, $this->cursor);
    }
}
