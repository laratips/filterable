<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine;

use JetBrains\PhpStorm\Pure;
use Laratips\Filterable\RuleEngine\Token\ClosingParenthesis;
use Laratips\Filterable\RuleEngine\Token\DateRange;
use Laratips\Filterable\RuleEngine\Token\Equal;
use Laratips\Filterable\RuleEngine\Token\EscapedString;
use Laratips\Filterable\RuleEngine\Token\Greater;
use Laratips\Filterable\RuleEngine\Token\GreaterOrEqual;
use Laratips\Filterable\RuleEngine\Token\Like;
use Laratips\Filterable\RuleEngine\Token\LogicalAnd;
use Laratips\Filterable\RuleEngine\Token\LogicalOr;
use Laratips\Filterable\RuleEngine\Token\NotEqual;
use Laratips\Filterable\RuleEngine\Token\Number;
use Laratips\Filterable\RuleEngine\Token\OpeningParenthesis;
use Laratips\Filterable\RuleEngine\Token\Smaller;
use Laratips\Filterable\RuleEngine\Token\SmallerOrEqual;
use Laratips\Filterable\RuleEngine\Token\Space;
use Laratips\Filterable\RuleEngine\Token\UnquotedString;
use Laratips\Filterable\RuleEngine\Token\Variable;

final class FilterGrammar implements GrammarInterface
{
    #[Pure]
    public function getDefinition(): array
    {
        return [
            [LogicalAnd::class, '&', 90],
            [LogicalOr::class, '\|', 85],
            [NotEqual::class, '<>|!=', 80],
            [Equal::class, '=', 75],
            [DateRange::class, '\d{2}\/\d{2}\/\d{4}-\d{2}\/\d{2}\/\d{4}', 70],
            [Number::class, '-?\d+(?:\.\d+)?', 65],
            [EscapedString::class, '"[^"]*"|\'[^\']*\'', 60],
            [Like::class, '%', 55],
            [SmallerOrEqual::class, '<=', 50],
            [GreaterOrEqual::class, '>=', 45],
            [Smaller::class, '<', 40],
            [Greater::class, '>', 35],
            [OpeningParenthesis::class, '\(', 30],
            [ClosingParenthesis::class, '\)', 25],
            [Space::class, '\s+', 20],
            [UnquotedString::class, '[\p{L}\p{N}_]+', 15],
            [Variable::class, '[a-zA-Z_]\w*', 10],
        ];
    }
}
