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

interface PathFactoryInterface
{
    /**
     * Creates a new path instance from its string representation.
     *
     * An set of supported path separators can optionally be supplied. This
     * defaults to only forward slashes (i.e. '/').
     *
     * @param string             $path
     * @param mixed<string>|null $separators
     *
     * @return PathInterface
     */
    public function fromString($path, $separators = null);

    /**
     * Creates a new path instance from a set of path atoms.
     *
     * Unless otherwise specified, created paths will be absolute, and have no
     * trailing separator.
     *
     * @param mixed<string> $atoms
     * @param boolean|null  $isAbsolute
     * @param boolean|null  $hasTrailingSeparator
     *
     * @return PathInterface
     * @throws InvalidPathAtomExceptionInterface If any supplied atom is invalid.
     */
    public function fromAtoms($atoms, $isAbsolute = null, $hasTrailingSeparator = null);
}
