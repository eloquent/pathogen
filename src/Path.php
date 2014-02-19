<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen;

/**
 * A static utility class for constructing generic paths.
 *
 * Do not use this class in type hints; use PathInterface instead.
 */
abstract class Path
{
    /**
     * Creates a new path instance from its string representation.
     *
     * @param string $path The string representation of the path.
     *
     * @return PathInterface The newly created path instance.
     */
    public static function fromString($path)
    {
        return static::factory()->create($path);
    }

    /**
     * Creates a new path instance from a set of path atoms.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param boolean|null  $isAbsolute           True if the path is absolute.
     * @param boolean|null  $hasTrailingSeparator True if the path has a trailing separator.
     *
     * @return PathInterface                               The newly created path instance.
     * @throws Exception\InvalidPathAtomExceptionInterface If any of the supplied atoms are invalid.
     */
    public static function fromAtoms(
        $atoms,
        $isAbsolute = null,
        $hasTrailingSeparator = null
    ) {
        return static::factory()->createFromAtoms(
            $atoms,
            $isAbsolute,
            $hasTrailingSeparator
        );
    }

    /**
     * Get the most appropriate path factory for this type of path.
     *
     * @return Factory\PathFactoryInterface The path factory.
     */
    protected static function factory()
    {
        return Factory\PathFactory::instance();
    }
}
