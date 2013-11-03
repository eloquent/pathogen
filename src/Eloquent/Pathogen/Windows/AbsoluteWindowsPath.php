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
use Eloquent\Pathogen\FileSystem\AbsoluteFileSystemPathInterface;
use Eloquent\Pathogen\Normalizer\PathNormalizerInterface;
use Eloquent\Pathogen\PathInterface;

/**
 * Represents an absolute Windows path.
 */
class AbsoluteWindowsPath extends AbsolutePath implements
    AbsoluteFileSystemPathInterface,
    AbsoluteWindowsPathInterface
{
    /**
     * Construct a new path instance.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param string|null   $drive                The drive specifier, or null if the path has no drive specifier.
     * @param boolean|null  $hasTrailingSeparator True if this path has a trailing separator.
     *
     * @throws Exception\InvalidDriveSpecifierException If the drive specifier is invalid.
     * @throws InvalidPathAtomExceptionInterface        If any of the supplied path atoms are invalid.
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
     * @return string|null The drive specifier, or null if this path does not have a drive specifier.
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
     * @return string|null $drive The drive specifier to use, or null to remove the drive specifier.
     *
     * @return AbsoluteWindowsPathInterface A new path instance with the supplied drive specifier joined to this path.
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

    /**
     * Get the parent of this path a specified number of levels up.
     *
     * @param integer|null $numLevels The number of levels up. Defaults to 1.
     *
     * @return PathInterface The parent of this path $numLevels up.
     */
    public function parent($numLevels = null)
    {
        return parent::parent($numLevels)->normalize();
    }

    // Implementation of AbsolutePathInterface =================================

    /**
     * Determine if this path is the direct parent of the supplied path.
     *
     * @param AbsolutePathInterface $path The child path.
     *
     * @return boolean True if this path is the direct parent of the supplied path.
     */
    public function isParentOf(AbsolutePathInterface $path)
    {
        if (!$this->driveSpecifiersMatch($this, $path)) {
            return false;
        }

        return parent::isParentOf($path);
    }

    /**
     * Determine if this path is an ancestor of the supplied path.
     *
     * @param AbsolutePathInterface $path The child path.
     *
     * @return boolean True if this path is an ancestor of the supplied path.
     */
    public function isAncestorOf(AbsolutePathInterface $path)
    {
        if (!$this->driveSpecifiersMatch($this, $path)) {
            return false;
        }

        return parent::isAncestorOf($path);
    }

    /**
     * Determine the shortest path from the supplied path to this path.
     *
     * For example, given path A equal to '/foo/bar', and path B equal to
     * '/foo/baz', A relative to B would be '../bar'.
     *
     * @param AbsolutePathInterface $path The path that the generated path will be relative to.
     *
     * @return RelativePathInterface A relative path from the supplied path to this path.
     */
    public function relativeTo(AbsolutePathInterface $path)
    {
        $thisDrive = $this->normalizePathDriveSpecifier($this);
        $pathDrive = $this->normalizePathDriveSpecifier($path);
        if ($thisDrive !== $pathDrive) {
            throw new Exception\DriveMismatchException(
                $thisDrive,
                $pathDrive
            );
        }

        return parent::relativeTo($path);
    }

    // Implementation details ==================================================

    /**
     * Validates a single path atom.
     *
     * This method is called internally by the constructor upon instantiation.
     * It can be overridden in child classes to change how path atoms are
     * validated.
     *
     * @param string $atom The atom to validate.
     *
     * @throws Exception\EmptyPathAtomException             If the path atom is empty.
     * @throws Exception\PathAtomContainsSeparatorException If the path atom contains a separator.
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
     * Get the normalized form of the drive specifier for the supplied path.
     *
     * @param AbsolutePathInterface $path The path.
     *
     * @return string|null The normalized drive specifier.
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
     * Returns true if the path specifiers for the given paths match.
     *
     * @param AbsolutePathInterface $left  The first path.
     * @param AbsolutePathInterface $right The second path.
     *
     * @return boolean True if the drive specifiers match.
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
     * Creates a new path instance of the most appropriate type.
     *
     * This method is called internally every time a new path instance is
     * created as part of another method call. It can be overridden in child
     * classes to change which classes are used when creating new path
     * instances.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param boolean       $isAbsolute           True if the new path should be absolute.
     * @param boolean|null  $hasTrailingSeparator True if the new path should have a trailing separator.
     *
     * @return PathInterface The newly created path instance.
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

        return static::factory()->createFromAtoms(
            $atoms,
            false,
            $hasTrailingSeparator
        );
    }

    /**
     * Create a new absolute Windows path with a drive specifier.
     *
     * This method is called internally every time a new path instance with a
     * drive specifier is created as part of another method call. It can be
     * overridden in child classes to change which classes are used when
     * creating new path instances.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param string|null   $drive                The drive specifier.
     * @param boolean|null  $hasTrailingSeparator True if the new path should have a trailing separator.
     *
     * @return AbsoluteWindowsPathInterface The newly created path instance.
     */
    protected function createPathWithDrive(
        $atoms,
        $drive,
        $hasTrailingSeparator = null
    ) {
        return static::factory()->createFromDriveAndAtoms(
            $atoms,
            $drive,
            true,
            $hasTrailingSeparator
        );
    }

    /**
     * Get the most appropriate path factory for this type of path.
     *
     * @return PathFactoryInterface The path factory.
     */
    protected static function factory()
    {
        return Factory\WindowsPathFactory::instance();
    }

    /**
     * Get the most appropriate path normalizer for this type of path.
     *
     * @return PathNormalizerInterface The path normalizer.
     */
    protected static function normalizer()
    {
        return Normalizer\WindowsPathNormalizer::instance();
    }

    private $drive;
}
