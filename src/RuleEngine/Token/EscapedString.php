<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine\Token;

use function mb_substr;

class EscapedString extends ValueToken
{
    public function getValue(): string
    {
        return mb_substr(parent::getValue(), 1, -1);
    }
}
