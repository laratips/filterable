<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine\Token;

final class UnquotedString extends ValueToken
{
    public function getValue(): string
    {
        return $this->value;
    }
}
