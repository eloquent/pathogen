<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows\Factory;

use Eloquent\Pathogen\AbstractPath;
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
     * Get a static instance of this path factory.
     *
     * @return WindowsPathFactoryInterface The static path factory.
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
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
        $drive = null;
        $isAbsolute = false;
        $isAnchored = false;
        $hasTrailingSeparator = false;

        if ('' === $path) {
            $atoms = array(AbstractPath::SELF_ATOM);
        } else {
            $atoms = preg_split('{[/\\\\]}', $path);
        }

        if (preg_match('{^([a-zA-Z]):}', $atoms[0], $matches)) {
            $drive = $matches[1];

            $atoms[0] = substr($atoms[0], 2);
            if (false === $atoms[0]) {
                $atoms[0] = '';
            }
        }

        $numAtoms = count($atoms);

        if ($numAtoms > 1) {
            if ('' === $atoms[0]) {
                if (null === $drive) {
                    $isAnchored = true;
                } else {
                    $isAbsolute = true;
                }

                array_shift($atoms);
                --$numAtoms;
            }
        } else {
            $isAbsolute = null !== $drive && '' === $atoms[0];
        }

        if ($numAtoms > 1) {
            if ('' === $atoms[$numAtoms - 1]) {
                $hasTrailingSeparator = true;
                array_pop($atoms);
            }
        }

        return $this->createFromDriveAndAtoms(
            array_filter($atoms, 'strlen'),
            $drive,
            $isAbsolute,
            $isAnchored,
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
        return $this->createFromDriveAndAtoms(
            $atoms,
            null,
            $isAbsolute,
            false,
            $hasTrailingSeparator
        );
    }

    // Implementation of WindowsPathFactoryInterface ===========================

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
     * @throws InvalidPathStateException         If the supplied arguments would produce an invalid path.
     */
    public function createFromDriveAndAtoms(
        $atoms,
        $drive = null,
        $isAbsolute = null,
        $isAnchored = null,
        $hasTrailingSeparator = null
    ) {
        if (null === $isAnchored) {
            $isAnchored = false;
        }
        if (null === $isAbsolute) {
            $isAbsolute = null !== $drive && !$isAnchored;
        }

        if ($isAbsolute && $isAnchored) {
            throw new InvalidPathStateException(
                'Absolute Windows paths cannot be anchored.'
            );
        }

        if ($isAbsolute) {
            return new AbsoluteWindowsPath(
                $drive,
                $atoms,
                $hasTrailingSeparator
            );
        }

        return new RelativeWindowsPath(
            $atoms,
            $drive,
            $isAnchored,
            $hasTrailingSeparator
        );
    }

    private static $instance;
    private $defaultDrive;
}
