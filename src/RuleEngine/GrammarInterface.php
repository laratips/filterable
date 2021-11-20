<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine;

interface GrammarInterface
{
    public function getDefinition(): array;
}
