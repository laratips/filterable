<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine\Token;

use JetBrains\PhpStorm\Pure;

final class Like extends Token
{
    #[Pure]
    public function __construct(public mixed $value, public ?int $cursor = null)
    {
        parent::__construct(Token::TYPE_OPERATOR, $this->value, $this->cursor);
    }
    
    public function getValue(): string
    {
        return 'like';
    }
}
