<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Factory;

use Eloquent\Pathogen\AbsolutePath;
use Eloquent\Pathogen\AbstractPath;
use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\Exception\InvalidPathStateException;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\RelativePath;

/**
 * A path factory that creates generic, Unix-style path instances.
 */
class PathFactory implements PathFactoryInterface
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
     * Creates a new path instance from its string representation.
     *
     * @param string $path The string representation of the path.
     *
     * @return PathInterface The newly created path instance.
     */
    public function create($path)
    {
        if ('' === $path) {
            $path = AbstractPath::SELF_ATOM;
        }

        $isAbsolute = false;
        $hasTrailingSeparator = false;

        $atoms = explode(AbstractPath::ATOM_SEPARATOR, $path);
        $numAtoms = count($atoms);

        if ($numAtoms > 1) {
            if ('' === $atoms[0]) {
                $isAbsolute = true;
                array_shift($atoms);
                --$numAtoms;
            }

            if ('' === $atoms[$numAtoms - 1]) {
                $hasTrailingSeparator = !$isAbsolute || $numAtoms > 1;
                array_pop($atoms);
            }
        }

        return $this->createFromAtoms(
            array_filter($atoms, 'strlen'),
            $isAbsolute,
            $hasTrailingSeparator
        );
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
            return new AbsolutePath($atoms, $hasTrailingSeparator);
        }

        return new RelativePath($atoms, $hasTrailingSeparator);
    }

    private static $instance;
}
