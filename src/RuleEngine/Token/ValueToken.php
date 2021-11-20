<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine\Token;

use JetBrains\PhpStorm\Pure;

class ValueToken extends Token
{
    #[Pure]
    public function __construct(public mixed $value, public ?int $cursor = null)
    {
        parent::__construct(Token::TYPE_VALUE, $this->value, $this->cursor);
    }
}
