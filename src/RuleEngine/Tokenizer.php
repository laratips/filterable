<?php

declare(strict_types=1);

namespace Laratips\Filterable\RuleEngine;

use ArrayIterator;
use Ds\PriorityQueue;

use function implode;
use function is_int;
use function preg_match;
use function preg_replace;
use function strlen;

final class Tokenizer
{
    private array $tokens = [];
    private ?string $compiledRegex = null;

    public function __construct(private GrammarInterface $grammar)
    {
        foreach ($this->grammar->getDefinition() as [$class, $regex, $priority]) {
            $this->registerToken($class, $regex, $priority);
        }
    }

    public function tokenize(string $expression): ArrayIterator
    {
        $regex = $this->getRegex();
        $stack = [];
        $offset = 0;
        $tokenNamespace = 'Laratips\\Filterable\\RuleEngine\\Token\\';

        while (preg_match($regex, $expression, $matches, PREG_NO_ERROR, $offset)) {
            $token = $this->getMatchedToken($matches);
            $classname = $tokenNamespace . $token;

            $stack[] = new $classname($matches[$token], $offset);
            $offset += strlen($matches[0]);
        }

        return new ArrayIterator($stack);
    }

    private function getMatchedToken(array $matches): string {
        foreach ($matches as $key => $value) {
            if ($value !== '' && !is_int($key)) {
                return $key;
            }
        }

        return 'Unknown';
    }

    private function registerToken(string $class, string $regex, int $priority): void
    {
        $this->tokens[$class] = new class($class, $regex, $priority) {
            public function __construct(public string $class, public string $regex, public int $priority)
            {
            }
        };
    }

    private function getRegex(): string
    {
        if ($this->compiledRegex === null) {
            $regexes = [];

            foreach ($this->getQueue() as $token) {
                $name = preg_replace('/^(\w+\\\)*/', '', $token->class);
                $regexes[] = "(?<$name>$token->regex)";
            }

            $this->compiledRegex = '~(' . implode('|', $regexes) . ')~Asu';
        }

        return $this->compiledRegex;
    }

    private function getQueue(): PriorityQueue
    {
        $queue = new PriorityQueue();

        foreach ($this->tokens as $token) {
            $queue->push($token, $token->priority);
        }

        return $queue;
    }
}
