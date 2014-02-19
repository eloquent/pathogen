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

use Eloquent\Pathogen\Exception\EmptyPathAtomException;
use Eloquent\Pathogen\Exception\EmptyPathException;
use Eloquent\Pathogen\Exception\InvalidPathAtomCharacterException;
use Eloquent\Pathogen\Exception\InvalidPathAtomExceptionInterface;
use Eloquent\Pathogen\Exception\InvalidPathStateException;
use Eloquent\Pathogen\Exception\PathAtomContainsSeparatorException;
use Eloquent\Pathogen\FileSystem\RelativeFileSystemPathInterface;
use Eloquent\Pathogen\PathInterface;
use Eloquent\Pathogen\RelativePath;
use Eloquent\Pathogen\RelativePathInterface;

/**
 * Represents a relative Windows path.
 */
class RelativeWindowsPath extends RelativePath implements
    RelativeFileSystemPathInterface,
    RelativeWindowsPathInterface
{
    /**
     * Creates a new relative Windows path from a set of path atoms and a drive
     * specifier.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param string|null   $drive                The drive specifier.
     * @param boolean|null  $isAnchored           True if the path is anchored to the drive root.
     * @param boolean|null  $hasTrailingSeparator True if the path has a trailing separator.
     *
     * @return WindowsPathInterface              The newly created path instance.
     * @throws InvalidPathAtomExceptionInterface If any of the supplied atoms are invalid.
     */
    public static function fromDriveAndAtoms(
        $atoms,
        $drive = null,
        $isAnchored = null,
        $hasTrailingSeparator = null
    ) {
        return static::factory()->createFromDriveAndAtoms(
            $atoms,
            $drive,
            false,
            $isAnchored,
            $hasTrailingSeparator
        );
    }

    /**
     * Construct a new relative Windows path instance.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param string|null   $drive                The drive specifier.
     * @param boolean|null  $isAnchored           True if this path is anchored to the drive root.
     * @param boolean|null  $hasTrailingSeparator True if this path has a trailing separator.
     *
     * @throws EmptyPathException                If the path atoms are empty, and the path is not anchored.
     * @throws InvalidPathAtomExceptionInterface If any of the supplied path atoms are invalid.
     */
    public function __construct(
        $atoms,
        $drive = null,
        $isAnchored = null,
        $hasTrailingSeparator = null
    ) {
        if (null === $isAnchored) {
            $isAnchored = false;
        }
        if (null === $hasTrailingSeparator) {
            $hasTrailingSeparator = false;
        }

        if (!$isAnchored && count($atoms) < 1) {
            throw new EmptyPathException;
        }

        if (null !== $drive) {
            $this->validateDriveSpecifier($drive);
        }

        parent::__construct($atoms, $hasTrailingSeparator);

        $this->drive = $drive;
        $this->isAnchored = $isAnchored;
    }

    // Implementation of WindowsPathInterface ==================================

    /**
     * Get this path's drive specifier.
     *
     * Absolute Windows paths always have a drive specifier, and will never
     * return null for this method.
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
     * Absolute Windows paths always have a drive specifier, and will always
     * return true for this method.
     *
     * @return boolean True is this path has a drive specifier.
     */
    public function hasDrive()
    {
        return null !== $this->drive();
    }

    /**
     * Returns true if this path's drive specifier is equal to the supplied
     * drive specifier.
     *
     * This method is not case sensitive.
     *
     * @param string|null $drive The driver specifier to compare to.
     *
     * @return boolean True if the drive specifiers are equal.
     */
    public function matchesDrive($drive)
    {
        return $this->driveSpecifiersMatch($this->drive(), $drive);
    }

    /**
     * Returns true if this path's drive specifier matches the supplied drive
     * specifier, or if either drive specifier is null.
     *
     * This method is not case sensitive.
     *
     * @param string|null $drive The driver specifier to compare to.
     *
     * @return boolean True if the drive specifiers match, or either drive specifier is null.
     */
    public function matchesDriveOrNull($drive)
    {
        return null === $drive ||
            !$this->hasDrive() ||
            $this->driveSpecifiersMatch($this->drive(), $drive);
    }

    /**
     * Joins the supplied drive specifier to this path.
     *
     * @return string|null $drive The drive specifier to use, or null to remove the drive specifier.
     *
     * @return WindowsPathInterface A new path instance with the supplied drive specifier joined to this path.
     */
    public function joinDrive($drive)
    {
        if (null === $drive) {
            return $this->createPathFromDriveAndAtoms(
                $this->atoms(),
                null,
                false,
                $this->isAnchored(),
                false
            );
        }

        return $this->createPathFromDriveAndAtoms(
            $this->atoms(),
            $drive,
            $this->isAnchored(),
            false,
            false
        );
    }

    // Implementation of RelativeWindowsPathInterface ==========================

    /**
     * Returns true if this path is 'anchored' to the drive root.
     *
     * This is a special case to represent almost-absolute Windows paths where
     * the drive is not present, but the path is still specified as starting
     * from the root of the drive.
     *
     * For example, the Windows path `\path\to\foo` represents the path
     * `C:\path\to\foo` when resolved against the `C:` drive.
     *
     * @return boolean True if this path is anchored.
     */
    public function isAnchored()
    {
        return $this->isAnchored;
    }

    // Implementation of RelativePathInterface =================================

    /**
     * Determine whether this path is the self path.
     *
     * The self path is a relative path with a single self atom (i.e. a dot
     * '.').
     *
     * @return boolean True if this path is the self path.
     */
    public function isSelf()
    {
        return !$this->isAnchored() && !$this->hasDrive() && parent::isSelf();
    }

    // Implementation of PathInterface =========================================

    /**
     * Generate a string representation of this path.
     *
     * @return string A string representation of this path.
     */
    public function string()
    {
        return
            ($this->hasDrive() ? $this->drive() . ':' : '') .
            ($this->isAnchored() ? static::ATOM_SEPARATOR : '') .
            implode(static::ATOM_SEPARATOR, $this->atoms()) .
            ($this->hasTrailingSeparator() ? static::ATOM_SEPARATOR : '');
    }

    /**
     * Joins the supplied path to this path.
     *
     * @param RelativePathInterface $path The path whose atoms should be joined to this path.
     *
     * @return PathInterface                    A new path with the supplied path suffixed to this path.
     * @throws Exception\DriveMismatchException If the supplied path has a drive that does not match this path's drive.
     */
    public function join(RelativePathInterface $path)
    {
        if ($path instanceof RelativeWindowsPathInterface) {
            if (!$this->matchesDriveOrNull($this->pathDriveSpecifier($path))) {
                throw new Exception\DriveMismatchException(
                    $this->drive(),
                    $path->drive()
                );
            }

            if ($path->isAnchored()) {
                return $path->joinDrive($this->drive());
            }
        }

        return parent::join($path);
    }

    /**
     * Get an absolute version of this path.
     *
     * If this path is relative, a new absolute path with equivalent atoms will
     * be returned. Otherwise, this path will be retured unaltered.
     *
     * @return AbsolutePathInterface     An absolute version of this path.
     * @throws InvalidPathStateException If absolute conversion is not possible for this path.
     */
    public function toAbsolute()
    {
        if (!$this->hasDrive()) {
            throw new InvalidPathStateException(
                'Cannot convert relative Windows path to absolute without a ' .
                    'drive specifier.'
            );
        }

        return $this->createPathFromDriveAndAtoms(
            $this->atoms(),
            $this->drive(),
            true,
            false,
            false
        );
    }

    // Implementation details ==================================================

    /**
     * Normalizes and validates a sequence of path atoms.
     *
     * This method is called internally by the constructor upon instantiation.
     * It can be overridden in child classes to change how path atoms are
     * normalized and/or validated.
     *
     * @param mixed<string> $atoms The path atoms to normalize.
     *
     * @return array<string>                      The normalized path atoms.
     * @throws EmptyPathAtomException             If any path atom is empty.
     * @throws PathAtomContainsSeparatorException If any path atom contains a separator.
     */
    protected function normalizeAtoms($atoms)
    {
        $normalizedAtoms = array();
        foreach ($atoms as $atom) {
            $this->validateAtom($atom);
            $normalizedAtoms[] = $atom;
        }

        return $normalizedAtoms;
    }

    /**
     * Validates a single path atom.
     *
     * This method is called internally by the constructor upon instantiation.
     * It can be overridden in child classes to change how path atoms are
     * validated.
     *
     * @param string $atom The atom to validate.
     *
     * @throws InvalidPathAtomExceptionInterface If an invalid path atom is encountered.
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
     * Validates the suppled drive specifier.
     *
     * @param string $drive The drive specifier to validate.
     *
     * @throws Exception\InvalidDriveSpecifierException If the drive specifier is invalid.
     */
    protected function validateDriveSpecifier($drive)
    {
        if (!preg_match('{^[a-zA-Z]$}', $drive)) {
            throw new Exception\InvalidDriveSpecifierException($drive);
        }
    }

    /**
     * Get the normalized form of the supplied drive specifier.
     *
     * @param string|null $drive The drive specifier to normalize.
     *
     * @return string|null The normalized drive specifier.
     */
    protected function normalizeDriveSpecifier($drive)
    {
        if (null === $drive) {
            return null;
        }

        return strtoupper($drive);
    }

    /**
     * Returns true if the supplied path specifiers match.
     *
     * @param string|null $left  The first specifier.
     * @param string|null $right The second specifier.
     *
     * @return boolean True if the drive specifiers match.
     */
    protected function driveSpecifiersMatch($left, $right)
    {
        return $this->normalizeDriveSpecifier($left) ===
            $this->normalizeDriveSpecifier($right);
    }

    /**
     * Get the the drive specifier of the supplied path, returning null if the
     * path is a non-Windows path.
     *
     * @param PathInterface $path The path.
     *
     * @return string|null The drive specifier.
     */
    protected function pathDriveSpecifier(PathInterface $path)
    {
        if ($path instanceof WindowsPathInterface) {
            return $path->drive();
        }

        return null;
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
            return $this->createPathFromDriveAndAtoms(
                $atoms,
                $this->drive(),
                true,
                false,
                $hasTrailingSeparator
            );
        }

        return $this->createPathFromDriveAndAtoms(
            $atoms,
            $this->drive(),
            false,
            $this->isAnchored(),
            $hasTrailingSeparator
        );
    }

    /**
     * Creates a new path instance of the most appropriate type from a set of
     * path atoms and a drive specifier.
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
    protected function createPathFromDriveAndAtoms(
        $atoms,
        $drive,
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
    private $isAnchored;
}
