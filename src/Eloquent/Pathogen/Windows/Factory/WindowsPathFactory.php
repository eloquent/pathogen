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
use Eloquent\Pathogen\Exception\InvalidPathStateException;
use Eloquent\Pathogen\Factory\PathFactory;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\Windows\AbsoluteWindowsPath;
use Eloquent\Pathogen\Windows\RelativeWindowsPath;

/**
 * A path factory that creates Windows path instances.
 */
class WindowsPathFactory extends PathFactory implements
    WindowsPathFactoryInterface
{
    /**
     * Construct a new Windows path factory.
     *
     * @param string $defaultDrive The default drive specifier to use when none
     *     is specified, or null to leave the drive specifier empty.
     */
    public function __construct($defaultDrive = null)
    {
        $this->defaultDrive = $defaultDrive;
    }

    /**
     * Get the default drive specifier.
     *
     * @return string|null The default drive specifier.
     */
    public function defaultDrive()
    {
        return $this->defaultDrive;
    }

    // Implementation of PathFactoryInterface ==================================

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
            $path = PathInterface::SELF_ATOM;
        }

        $isAbsolute = false;
        $drive = null;
        $hasTrailingSeparator = false;

        $atoms = preg_split('~[/\\\\]~', $path);
        if (preg_match('/^([a-zA-Z]):$/', $atoms[0], $matches)) {
            $isAbsolute = true;
            $drive = $matches[1];
            array_shift($atoms);
        }
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
                --$numAtoms;
            }
        }

        foreach ($atoms as $index => $atom) {
            if ('' === $atom) {
                array_splice($atoms, $index, 1);
                --$numAtoms;
            }
        }

        return $this->createFromDriveAndAtoms(
            $atoms,
            $drive,
            $isAbsolute,
            $hasTrailingSeparator
        );
    }

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
    ) {
        return $this->createFromDriveAndAtoms(
            $atoms,
            $isAbsolute ? $this->defaultDrive() : null,
            $isAbsolute,
            $hasTrailingSeparator
        );
    }

    // Implementation of WindowsPathFactoryInterface ===========================

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
     * @param boolean|null  $hasTrailingSeparator True if the path has a
     *     trailing separator.
     *
     * @return PathInterface                     The newly created path instance.
     * @throws InvalidPathAtomExceptionInterface If any of the supplied atoms
     *     are invalid.
     */
    public function createFromDriveAndAtoms(
        $atoms,
        $drive,
        $isAbsolute = null,
        $hasTrailingSeparator = null
    ) {
        if (!$isAbsolute && null !== $drive) {
            throw new InvalidPathStateException(
                "Path cannot be relative and have a drive specifier."
            );
        }

        if ($isAbsolute) {
            return new AbsoluteWindowsPath(
                $atoms,
                $drive,
                $hasTrailingSeparator
            );
        }

        return new RelativeWindowsPath($atoms, $hasTrailingSeparator);
    }

    private $defaultDrive;
}
