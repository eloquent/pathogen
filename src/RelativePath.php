<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2014 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Eloquent\Pathogen;

/**
 * Represents a relative path.
 */
class RelativePath extends AbstractPath implements RelativePathInterface
{
    /**
     * Creates a new relative path instance from its string representation.
     *
     * @param string $path The string representation of the relative path.
     *
     * @return RelativePathInterface              The newly created relative path instance.
     * @throws Exception\NonRelativePathException If the supplied string represents a non-relative path.
     */
    public static function fromString($path)
    {
        $pathObject = static::factory()->create($path);
        if (!$pathObject instanceof RelativePathInterface) {
            throw new Exception\NonRelativePathException($pathObject);
        }

        return $pathObject;
    }

    /**
     * Creates a new relative path from a set of path atoms.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param boolean|null  $hasTrailingSeparator True if the path has a trailing separator.
     *
     * @return RelativePathInterface                       The newly created relative path.
     * @throws Exception\InvalidPathAtomExceptionInterface If any of the supplied atoms are invalid.
     * @throws Exception\InvalidPathStateException         If the supplied arguments would produce an invalid path.
     */
    public static function fromAtoms($atoms, $hasTrailingSeparator = null)
    {
        return static::factory()->createFromAtoms(
            $atoms,
            false,
            $hasTrailingSeparator
        );
    }

    // Implementation of PathInterface =========================================

    /**
     * Get an absolute version of this path.
     *
     * If this path is relative, a new absolute path with equivalent atoms will
     * be returned. Otherwise, this path will be retured unaltered.
     *
     * @return AbsolutePathInterface               An absolute version of this path.
     * @throws Exception\InvalidPathStateException If absolute conversion is not possible for this path.
     */
    public function toAbsolute()
    {
        return $this->createPath(
            $this->atoms(),
            true,
            false
        );
    }

    /**
     * Get a relative version of this path.
     *
     * If this path is absolute, a new relative path with equivalent atoms will
     * be returned. Otherwise, this path will be retured unaltered.
     *
     * @return RelativePathInterface        A relative version of this path.
     * @throws Exception\EmptyPathException If this path has no atoms.
     */
    public function toRelative()
    {
        return $this;
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
        $atoms = $this->normalize()->atoms();

        return 1 === count($atoms) && static::SELF_ATOM === $atoms[0];
    }

    /**
     * Resolve this path against the supplied path.
     *
     * @param AbsolutePathInterface $basePath The path to resolve against.
     *
     * @return AbsolutePathInterface The resolved path.
     */
    public function resolveAgainst(AbsolutePathInterface $basePath)
    {
        return static::resolver()->resolve($basePath, $this);
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
     * @return array<string>                                The normalized path atoms.
     * @throws Exception\EmptyPathAtomException             If any path atom is empty.
     * @throws Exception\PathAtomContainsSeparatorException If any path atom contains a separator.
     */
    protected function normalizeAtoms($atoms)
    {
        $atoms = parent::normalizeAtoms($atoms);
        if (count($atoms) < 1) {
            throw new Exception\EmptyPathException;
        }

        return $atoms;
    }
}
