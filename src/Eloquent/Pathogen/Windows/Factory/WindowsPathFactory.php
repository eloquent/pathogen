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
use Eloquent\Pathogen\Factory\PathFactoryInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\Windows\AbsoluteWindowsPath;

class WindowsPathFactory extends PathFactory implements
    WindowsPathFactoryInterface
{
    /**
     * @param string $defaultDrive
     */
    public function __construct($defaultDrive = null)
    {
        $this->defaultDrive = $defaultDrive;
    }

    /**
     * @return string|null
     */
    public function defaultDrive()
    {
        return $this->defaultDrive;
    }

    // Implementation of PathFactoryInterface ==================================

    /**
     * Creates a new path instance from its string representation.
     *
     * @param string $path
     *
     * @return PathInterface
     * @throws Exception\InvalidDriveSpecifierException If the drive specifier
     * is invalid
     */
    public function create($path)
    {
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
     * @param mixed<string> $atoms
     * @param boolean|null  $isAbsolute
     * @param boolean|null  $hasTrailingSeparator
     *
     * @return PathInterface
     * @throws InvalidPathAtomExceptionInterface If any supplied atom is
     * invalid.
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
     * @param mixed<string> $atoms
     * @param string|null   $drive
     * @param boolean|null  $isAbsolute
     * @param boolean|null  $hasTrailingSeparator
     *
     * @return PathInterface
     * @throws Exception\InvalidDriveSpecifierException If the drive specifier
     * is invalid
     * @throws InvalidPathAtomExceptionInterface If any supplied atom is
     * invalid.
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

        return parent::createFromAtoms(
            $atoms,
            $isAbsolute,
            $hasTrailingSeparator
        );
    }

    private $defaultDrive;
}
