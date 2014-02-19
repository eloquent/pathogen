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
 * Represents an absolute path.
 */
class AbsolutePath extends AbstractPath implements AbsolutePathInterface
{
    /**
     * Creates a new absolute path from its string representation.
     *
     * @param string $path The string representation of the absolute path.
     *
     * @return AbsolutePathInterface              The newly created absolute path.
     * @throws Exception\NonAbsolutePathException If the supplied string represents a non-absolute path.
     */
    public static function fromString($path)
    {
        $pathObject = static::factory()->create($path);
        if (!$pathObject instanceof AbsolutePathInterface) {
            throw new Exception\NonAbsolutePathException($pathObject);
        }

        return $pathObject;
    }

    /**
     * Creates a new absolute path from a set of path atoms.
     *
     * @param mixed<string> $atoms                The path atoms.
     * @param boolean|null  $hasTrailingSeparator True if the path has a trailing separator.
     *
     * @return AbsolutePathInterface                       The newly created absolute path.
     * @throws Exception\InvalidPathAtomExceptionInterface If any of the supplied atoms are invalid.
     * @throws Exception\InvalidPathStateException         If the supplied arguments would produce an invalid path.
     */
    public static function fromAtoms($atoms, $hasTrailingSeparator = null)
    {
        return static::factory()->createFromAtoms(
            $atoms,
            true,
            $hasTrailingSeparator
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
        return static::ATOM_SEPARATOR . parent::string();
    }

    /**
     * Adds a trailing slash to this path.
     *
     * @return PathInterface A new path instance with a trailing slash suffixed to this path.
     */
    public function joinTrailingSlash()
    {
        if (!$this->hasAtoms()) {
            return $this;
        }

        return parent::joinTrailingSlash();
    }

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
        return $this;
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
        return $this->createPath(
            $this->atoms(),
            false,
            false
        );
    }

    // Implementation of AbsolutePathInterface =================================

    /**
     * Determine whether this path is the root path.
     *
     * The root path is an absolute path with no atoms.
     *
     * @return boolean True if this path is the root path.
     */
    public function isRoot()
    {
        return !$this->normalize()->hasAtoms();
    }

    /**
     * Determine if this path is the direct parent of the supplied path.
     *
     * @param AbsolutePathInterface $path The child path.
     *
     * @return boolean True if this path is the direct parent of the supplied path.
     */
    public function isParentOf(AbsolutePathInterface $path)
    {
        return $path->hasAtoms() &&
            $this->normalize()->atoms() ===
                $path->parent()->normalize()->atoms();
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
        $parentAtoms = $this->normalize()->atoms();

        return $parentAtoms === array_slice(
            $path->normalize()->atoms(),
            0,
            count($parentAtoms)
        );
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
        $parentAtoms = $path->normalize()->atoms();
        $childAtoms = $this->normalize()->atoms();

        if ($childAtoms === $parentAtoms) {
            $diffAtoms = array(static::SELF_ATOM);
        } else {
            $diffAtoms = array_diff_assoc($childAtoms, $parentAtoms);
            $diffAtomIndices = array_keys($diffAtoms);
            $diffAtoms = array_slice(
                $childAtoms,
                array_shift($diffAtomIndices)
            );

            $fillCount =
                (count($parentAtoms) - count($childAtoms)) +
                count($diffAtoms);

            if ($fillCount > 0) {
                $diffAtoms = array_merge(
                    array_fill(0, $fillCount, static::PARENT_ATOM),
                    $diffAtoms
                );
            }
        }

        return $this->createPath($diffAtoms, false, false);
    }

    /**
     * Resolve the supplied path against this path.
     *
     * @param PathInterface $path The path to resolve.
     *
     * @return AbsolutePathInterface The resolved path.
     */
    public function resolve(PathInterface $path)
    {
        return static::resolver()->resolve($this, $path);
    }
}
