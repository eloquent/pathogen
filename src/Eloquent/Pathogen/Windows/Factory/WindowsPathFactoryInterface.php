<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows\Factory;

use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\Factory\PathFactoryInterface;

/**
 * The interface implemented by path factories that create Windows paths.
 */
interface WindowsPathFactoryInterface extends PathFactoryInterface
{
    /**
     * Creates a new path instance from a set of path atoms and a drive
     * specifier.
     *
     * Unless otherwise specified, created paths will be absolute, and have no
     * trailing separator.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param string|null   $drive                The drive specifier.
     * @param boolean|null  $isAbsolute           True if the path is absolute.
     * @param boolean|null  $hasTrailingSeparator True if the path has a trailing separator.
     *
     * @return WindowsPathInterface              The newly created path instance.
     * @throws InvalidPathAtomExceptionInterface If any of the supplied atoms are invalid.
     */
    public function createFromDriveAndAtoms(
        $atoms,
        $drive,
        $isAbsolute = null,
        $hasTrailingSeparator = null
    );
}
