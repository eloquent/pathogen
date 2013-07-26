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

use Eloquent\Pathogen\AbsolutePathInterface;
use Eloquent\Pathogen\Exception\InvalidPathAtomCharacterException;
use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException;
use Eloquent\Pathogen\FileSystem\AbstractAbsoluteFileSystemPath;
use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;

/**
 * Represents an absolute Windows path.
 */
class AbsoluteWindowsPath extends AbstractAbsoluteFileSystemPath implements
    AbsoluteWindowsPathInterface
{
    /**
     * Construct a new path instance.
     *
     * @param mixed<string> $atoms The path atoms.
     * @param string|null   $drive The drive specifier, or null if the path has
     *     no drive specifier.
     * @param boolean|null $hasTrailingSeparator True if this path has a
     *     trailing separator.
     *
     * @throws Exception\InvalidDriveSpecifierException If the drive specifier
     *     is invalid.
     * @throws InvalidPathAtomExceptionInterface If any of the supplied path
     *     atoms are invalid.
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
     * Get this path's drive specifier.
     *
     * @return string|null The drive specifier, or null if this path does not
     *     have a drive specifier.
     */
    public function drive()
    {
        return $this->drive;
    }

    /**
     * Determine whether this path has a drive specifier.
     *
     * @return boolean True is this path has a drive specifier.
     */
    public function hasDrive()
    {
        return null !== $this->drive();
    }

    /**
     * Joins the supplied drive specifier to this path.
     *
     * @return string|null $drive The drive specifier to use, or null to remove
     *     the drive specifier.
     *
     * @return AbsoluteWindowsPathInterface A new path instance with the
     *     supplied drive specifier joined to this path.
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
     * Generate a string representation of this path.
     *
     * @return string A string representation of this path.
     */
    public function string()
    {
        $drive = $this->drive();
        if (null !== $drive) {
            return
                $drive .
                ':' .
                static::ATOM_SEPARATOR .
                implode(static::ATOM_SEPARATOR, $this->atoms()) .
                ($this->hasTrailingSeparator() ? static::ATOM_SEPARATOR : '')
            ;
        }

        return
            static::ATOM_SEPARATOR .
            implode(static::ATOM_SEPARATOR, $this->atoms()) .
            ($this->hasTrailingSeparator() ? static::ATOM_SEPARATOR : '')
        ;
    }

    // Implementation of AbsolutePathInterface =================================

    /**
     * Determine if this path is the direct parent of the supplied path.
     *
     * @param AbsolutePathInterface        $path       The child path.
     * @param PathNormalizerInterface|null $normalizer The normalizer to use
     *     when determining the result.
     *
     * @return boolean True if this path is the direct parent of the supplied
     *     path.
     */
    public function isParentOf(
        AbsolutePathInterface $path,
        PathNormalizerInterface $normalizer = null
    ) {
        if (null === $normalizer) {
            $normalizer = $this->createDefaultNormalizer();
        }

        if (!$this->driveSpecifiersMatch($this, $path)) {
            return false;
        }

        return parent::isParentOf($path, $normalizer);
    }

    /**
     * Determine if this path is an ancestor of the supplied path.
     *
     * @param AbsolutePathInterface        $path       The child path.
     * @param PathNormalizerInterface|null $normalizer The normalizer to use
     *     when determining the result.
     *
     * @return boolean True if this path is an ancestor of the supplied path.
     */
    public function isAncestorOf(
        AbsolutePathInterface $path,
        PathNormalizerInterface $normalizer = null
    ) {
        if (null === $normalizer) {
            $normalizer = $this->createDefaultNormalizer();
        }

        if (!$this->driveSpecifiersMatch($this, $path)) {
            return false;
        }

        return parent::isAncestorOf($path, $normalizer);
    }

    /**
     * Determine the shortest path from the supplied path to this path.
     *
     * For example, given path A equal to '/foo/bar', and path B equal to
     * '/foo/baz', A relative to B would be '../bar'.
     *
     * @param AbsolutePathInterface $path The path that the generated path will
     *     be relative to.
     * @param PathNormalizerInterface|null $normalizer The normalizer to use
     *     when determining the result.
     *
     * @return RelativePathInterface A relative path from the supplied path to
     *     this path.
     */
    public function relativeTo(
        AbsolutePathInterface $path,
        PathNormalizerInterface $normalizer = null
    ) {
        if (null === $normalizer) {
            $normalizer = $this->createDefaultNormalizer();
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
    protected function driveSpecifiersMatch(
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
