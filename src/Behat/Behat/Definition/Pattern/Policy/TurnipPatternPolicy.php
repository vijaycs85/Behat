<?php

/*
 * This file is part of the Behat.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Behat\Behat\Definition\Pattern\Policy;

use Behat\Behat\Definition\Pattern\Pattern;
use Behat\Transliterator\Transliterator;

/**
 * Behat turnip pattern policy.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class TurnipPatternPolicy implements PatternPolicy
{
    const PLACEHOLDER_REGEXP = "/\\\:(\w+)/";
    const OPTIONAL_WORD_REGEXP = '/(\s)?\\\\\(([^\\\]+)\\\\\)(\s)?/';
    const ALTERNATIVE_WORD_REGEXP = '/(\w+)\\\\\/(\w+)/';

    /**
     * @var string[]
     */
    private static $placeholderPatterns = array(
        "/(?<=\s|^)\"[^\"]+\"(?=\s|$)/",
        "/(?<=\s|^)'[^']+'(?=\s|$)/",
        "/(?<=\s|^)\d+/"
    );

    /**
     * {@inheritdoc}
     */
    public function supportsPatternType($type)
    {
        return null === $type || 'turnip' === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function generatePattern($stepText)
    {
        $count = 0;
        $pattern = $stepText;
        foreach (self::$placeholderPatterns as $replacePattern) {
            $pattern = preg_replace_callback(
                $replacePattern,
                function () use (&$count) {
                    return ':arg' . ++$count;
                },
                $pattern
            );
        }
        $canonicalText = $this->generateCanonicalText($stepText);

        return new Pattern($canonicalText, $pattern, $count);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsPattern($pattern)
    {
        return '/' !== substr($pattern, 0, 1) || '/' !== substr($pattern, -1);
    }

    /**
     * {@inheritdoc}
     */
    public function transformPatternToRegex($pattern)
    {
        $regex = preg_quote($pattern, '/');

        // placeholder
        $regex = preg_replace_callback(
            self::PLACEHOLDER_REGEXP,
            function ($match) {
                return sprintf(
                    "[\"']?(?P<%s>(?<=\")[^\"]+(?=\")|(?<=')[^']+(?=')|(?<=\s)\w+(?=\s|$))['\"]?",
                    $match[1]
                );
            },
            $regex
        );

        // optional word
        $regex = preg_replace(self::OPTIONAL_WORD_REGEXP, '(?:\1)?(?:\2)?(?:\3)?', $regex);

        // alternative word
        $regex = preg_replace(self::ALTERNATIVE_WORD_REGEXP, '(?:\1|\2)', $regex);

        return '/^' . $regex . '$/';
    }

    /**
     * Generates canonical text for step text.
     *
     * @param string $stepText
     *
     * @return string
     */
    private function generateCanonicalText($stepText)
    {
        $canonicalText = preg_replace(self::$placeholderPatterns, '', $stepText);
        $canonicalText = Transliterator::transliterate($canonicalText, ' ');
        $canonicalText = preg_replace('/[^a-zA-Z\_\ ]/', '', $canonicalText);
        $canonicalText = str_replace(' ', '', ucwords($canonicalText));

        return $canonicalText;
    }
}
