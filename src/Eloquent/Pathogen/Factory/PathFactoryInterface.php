<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Factory;

use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\PathInterface;

/**
 * The interface implemented by path factories.
 */
interface PathFactoryInterface
{
    /**
     * Creates a new path instance from its string representation.
     *
     * @param string $path The string representation of the path.
     *
     * @return PathInterface The newly created path instance.
     */
    public function create($path);

    /**
     * Creates a new path instance from a set of path atoms.
     *
     * Unless otherwise specified, created paths will be absolute, and have no
     * trailing separator.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param boolean|null  $isAbsolute           True if the path is absolute.
     * @param boolean|null  $hasTrailingSeparator True if the path has a
     *     trailing separator.
     *
     * @return PathInterface                     The newly created path instance.
     * @throws InvalidPathAtomExceptionInterface If any of the supplied atoms
     *     are invalid.
     */
    public function createFromAtoms(
        $atoms,
        $isAbsolute = null,
        $hasTrailingSeparator = null
    );
}
