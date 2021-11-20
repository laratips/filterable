<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine\Token;

use JetBrains\PhpStorm\Pure;

final class Number extends ValueToken
{
    public function getValue(): float|int
    {
        $value = $this->value;
        if (!\is_numeric($value)) {
            throw new \Exception('Invalid numeric value: ' . $value);
        }

        if (\str_contains($value, '.')) {
            return (float) $value;
        }

        return (int) $value;
    }
}
