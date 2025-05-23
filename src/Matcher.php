<?php

declare(strict_types=1);

namespace Spiral\Translator;

/**
 * Example:
 * post.*
 * post.(save|delete)
 */
final class Matcher
{
    public function isPattern(string $string): bool
    {
        return \str_contains($string, '*') || \str_contains($string, '|');
    }

    /**
     * Checks if string matches given pattern.
     */
    public function matches(string $string, string $pattern): bool
    {
        return match (true) {
            $string === $pattern => true,
            !$this->isPattern($pattern) => false,
            default => (bool) \preg_match($this->getRegex($pattern), $string),
        };
    }

    private function getRegex(string $pattern): string
    {
        $regex = \str_replace('*', '[a-z0-9_\-]+', \addcslashes($pattern, '.-'));

        return "#^{$regex}$#i";
    }
}
