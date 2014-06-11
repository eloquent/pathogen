<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Unix\Factory;

use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\Exception\InvalidPathStateException;
use Eloquent\Pathogen\Factory\PathFactory;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\Unix\AbsoluteUnixPath;
use Eloquent\Pathogen\Unix\RelativeUnixPath;

/**
 * A path factory that creates Unix path instances.
 */
class UnixPathFactory extends PathFactory
{
    /**
     * Get a static instance of this path factory.
     *
     * @return PathFactoryInterface The static path factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Creates a new path instance from a set of path atoms.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param boolean|null  $isAbsolute           True if the path is absolute.
     * @param boolean|null  $hasTrailingSeparator True if the path has a trailing separator.
     *
     * @return PathInterface                     The newly created path instance.
     * @throws InvalidPathAtomExceptionInterface If any of the supplied atoms are invalid.
     * @throws InvalidPathStateException         If the supplied arguments would produce an invalid path.
     */
    public function createFromAtoms(
        $atoms,
        $isAbsolute = null,
        $hasTrailingSeparator = null
    ) {
        if (null === $isAbsolute) {
            $isAbsolute = false;
        }

        if ($isAbsolute) {
            return new AbsoluteUnixPath($atoms, $hasTrailingSeparator);
        }

        return new RelativeUnixPath($atoms, $hasTrailingSeparator);
    }

    private static $instance;
}
