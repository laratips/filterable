<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine\Token;

use function array_map;
use function explode;
use function strtotime;

class DateRange extends ValueToken
{
    public function getValue(): array
    {
        return array_map(static fn(string $value) => strtotime($value), explode('-', parent::getValue()));
    }
}
