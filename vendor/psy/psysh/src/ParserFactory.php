<?php

/*
 * This file is part of Psy Shell.
 *
 * (c) 2012-2023 Justin Hileman
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Psy;

use PhpParser\Parser;
use PhpParser\ParserFactory as OriginalParserFactory;

/**
 * Parser factory to abstract over PHP Parser library versions.
 */
class ParserFactory
{
    /**
<<<<<<< HEAD
     * Possible kinds of parsers for the factory, from PHP parser library.
     *
     * @return string[]
     */
    public static function getPossibleKinds(): array
=======
     * New parser instance.
     */
    public function createParser(): Parser
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    {
        $factory = new OriginalParserFactory();

<<<<<<< HEAD
    /**
     * Default kind (if supported, based on current interpreter's version).
     *
     * @return string|null
     */
    public function getDefaultKind()
    {
        return static::ONLY_PHP7;
    }

    /**
     * New parser instance with given kind.
     *
     * @param string|null $kind One of class constants (only for PHP parser 2.0 and above)
     */
    public function createParser($kind = null): Parser
    {
        $originalFactory = new OriginalParserFactory();

        $kind = $kind ?: $this->getDefaultKind();

        if (!\in_array($kind, static::getPossibleKinds())) {
            throw new \InvalidArgumentException('Unknown parser kind');
        }

        $parser = $originalFactory->create(\constant(OriginalParserFactory::class.'::'.$kind));

        return $parser;
=======
        if (!\method_exists($factory, 'createForHostVersion')) {
            return $factory->create(OriginalParserFactory::PREFER_PHP7);
        }

        return $factory->createForHostVersion();
>>>>>>> 6a554a825f521a86c6b530852924f3d817076498
    }
}
