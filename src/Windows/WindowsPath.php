<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows;

use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\Path;

/**
 * A static utility class for constructing Windows paths.
 *
 * Do not use this class in type hints; use WindowsPathInterface instead.
 */
abstract class WindowsPath extends Path
{
    /**
     * Creates a new path instance from a set of path atoms and a drive
     * specifier.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param string|null   $drive                The drive specifier.
     * @param boolean|null  $isAbsolute           True if the path is absolute.
     * @param boolean|null  $isAnchored           True if the path is anchored to the drive root.
     * @param boolean|null  $hasTrailingSeparator True if the path has a trailing separator.
     *
     * @return WindowsPathInterface              The newly created path instance.
     * @throws InvalidPathAtomExceptionInterface If any of the supplied atoms are invalid.
     */
    public static function fromDriveAndAtoms(
        $atoms,
        $drive = null,
        $isAbsolute = null,
        $isAnchored = null,
        $hasTrailingSeparator = null
    ) {
        return static::factory()->createFromDriveAndAtoms(
            $atoms,
            $drive,
            $isAbsolute,
            $isAnchored,
            $hasTrailingSeparator
        );
    }

    /**
     * Get the most appropriate path factory for this type of path.
     *
     * @return Factory\WindowsPathFactoryInterface The path factory.
     */
    protected static function factory()
    {
        return Factory\WindowsPathFactory::instance();
    }
}
