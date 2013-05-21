<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen\Windows;

use Eloquent\Pathogen\AbsolutePath;
use Eloquent\Pathogen\AbsolutePathInterface;
use Eloquent\Pathogen\Exception\InvalidPathAtomCharacterException;
use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException;
use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;

class AbsoluteWindowsPath extends AbsolutePath implements
    AbsoluteWindowsPathInterface
{
    /**
     * @param mixed<string> $atoms
     * @param string|null   $drive
     * @param boolean|null  $hasTrailingSeparator
     *
     * @throws Exception\InvalidDriveSpecifierException If the drive specifier
     * is invalid
     * @throws InvalidPathAtomExceptionInterface If any supplied atom is
     * invalid.
     */
    public function __construct($atoms, $drive, $hasTrailingSeparator = null)
    {
        if (null !== $drive && !preg_match('/^[a-zA-Z]$/', $drive)) {
            throw new Exception\InvalidDriveSpecifierException($drive);
        }

        parent::__construct($atoms, $hasTrailingSeparator);

        $this->drive = $drive;
    }

    // Implementation of AbsoluteWindowsPathInterface ==========================

    /**
     * @return string|null
     */
    public function drive()
    {
        return $this->drive;
    }

    /**
     * @return boolean
     */
    public function hasDrive()
    {
        return null !== $this->drive();
    }

    /**
     * @return string|null $drive
     *
     * @return AbsoluteWindowsPathInterface
     */
    public function joinDrive($drive)
    {
        return $this->createPathWithDrive(
            $this->atoms(),
            $drive,
            false
        );
    }

    // Implementation of PathInterface =========================================

    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function string()
    {
        $drive = $this->drive();
        if (null !== $drive) {
            return sprintf(
                '%s:%s%s%s',
                $drive,
                static::ATOM_SEPARATOR,
                implode(static::ATOM_SEPARATOR, $this->atoms()),
                $this->hasTrailingSeparator() ? static::ATOM_SEPARATOR : ''
            );
        }

        return sprintf(
            '%s%s%s',
            static::ATOM_SEPARATOR,
            implode(static::ATOM_SEPARATOR, $this->atoms()),
            $this->hasTrailingSeparator() ? static::ATOM_SEPARATOR : ''
        );
    }

    // Implementation of AbsolutePathInterface =================================

    /**
     * Returns true if this path is the direct parent of the supplied path.
     *
     * @param AbsolutePathInterface        $path
     * @param PathNormalizerInterface|null $normalizer
     *
     * @return boolean
     */
    public function isParentOf(
        AbsolutePathInterface $path,
        PathNormalizerInterface $normalizer = null
    ) {
        if (null == $normalizer) {
            $normalizer = new Normalizer\WindowsPathNormalizer;
        }

        if (!$this->driveLettersMatch($this, $path)) {
            return false;
        }

        return parent::isParentOf($path, $normalizer);
    }

    /**
     * Returns true if this path is an ancestor of the supplied path.
     *
     * @param AbsolutePathInterface        $path
     * @param PathNormalizerInterface|null $normalizer
     *
     * @return boolean
     */
    public function isAncestorOf(
        AbsolutePathInterface $path,
        PathNormalizerInterface $normalizer = null
    ) {
        if (null == $normalizer) {
            $normalizer = new Normalizer\WindowsPathNormalizer;
        }

        if (!$this->driveLettersMatch($this, $path)) {
            return false;
        }

        return parent::isAncestorOf($path, $normalizer);
    }

    /**
     * Returns a relative path from the supplied path to this path.
     *
     * For example, given path A equal to '/foo/bar', and path B equal to
     * '/foo/baz', A relative to B would be '../bar'.
     *
     * @param AbsolutePathInterface        $path
     * @param PathNormalizerInterface|null $normalizer
     *
     * @return RelativePathInterface
     */
    public function relativeTo(
        AbsolutePathInterface $path,
        PathNormalizerInterface $normalizer = null
    ) {
        if (null == $normalizer) {
            $normalizer = new Normalizer\WindowsPathNormalizer;
        }

        $thisDrive = $this->normalizePathDriveSpecifier($this);
        $pathDrive = $this->normalizePathDriveSpecifier($path);
        if ($thisDrive !== $pathDrive) {
            throw new Exception\DriveMismatchException(
                $thisDrive,
                $pathDrive
            );
        }

        return parent::relativeTo($path, $normalizer);
    }

    // Implementation details ==================================================

    /**
     * @param string $atom
     */
    protected function validateAtom($atom)
    {
        parent::validateAtom($atom);

        if (false !== strpos($atom, '\\')) {
            throw new PathAtomContainsSeparatorException($atom);
        } elseif (preg_match('/([\x00-\x1F<>:"|?*])/', $atom, $matches)) {
            throw new InvalidPathAtomCharacterException($atom, $matches[1]);
        }
    }

    /**
     * @param AbsolutePathInterface $path
     *
     * @return string|null
     */
    protected function normalizePathDriveSpecifier(AbsolutePathInterface $path)
    {
        if ($path instanceof AbsoluteWindowsPathInterface) {
            $drive = $path->drive();
            if (null !== $drive) {
                $drive = strtoupper($drive);
            }

            return $drive;
        }

        return null;
    }

    /**
     * @param AbsolutePathInterface $left
     * @param AbsolutePathInterface $right
     *
     * @return boolean
     */
    protected function driveLettersMatch(
        AbsolutePathInterface $left,
        AbsolutePathInterface $right
    ) {
        $leftDrive = $this->normalizePathDriveSpecifier($left);
        $rightDrive = $this->normalizePathDriveSpecifier($right);

        return $leftDrive === $rightDrive;
    }

    /**
     * @param mixed<string> $atoms
     * @param boolean       $isAbsolute
     * @param boolean|null  $hasTrailingSeparator
     *
     * @return PathInterface
     */
    protected function createPath(
        $atoms,
        $isAbsolute,
        $hasTrailingSeparator = null
    ) {
        if ($isAbsolute) {
            return $this->createPathWithDrive(
                $atoms,
                $this->drive(),
                $hasTrailingSeparator
            );
        }

        return new RelativeWindowsPath($atoms, $hasTrailingSeparator);
    }

    /**
     * @param mixed<string> $atoms
     * @param string|null   $drive
     * @param boolean|null  $hasTrailingSeparator
     *
     * @return PathInterface
     */
    protected function createPathWithDrive(
        $atoms,
        $drive,
        $hasTrailingSeparator = null
    ) {
        return new static(
            $atoms,
            $drive,
            $hasTrailingSeparator
        );
    }

    private $drive;
}
