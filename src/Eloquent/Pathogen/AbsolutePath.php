<?php

/*
 * This file is part of the Pathogen package.
 *
 * Copyright © 2013 Erin Millard
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eloquent\Pathogen;

/**
 * Represents an absolute path.
 */
class AbsolutePath extends AbstractPath implements AbsolutePathInterface
{
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
     * @return PathInterface A new path instance with a trailing slash suffixed
     *     to this path.
     */
    public function joinTrailingSlash()
    {
        if (!$this->hasAtoms()) {
            return $this;
        }

        return parent::joinTrailingSlash();
    }

    // Implementation of AbsolutePathInterface =================================

    /**
     * Determine whether this path is the root path.
     *
     * The root path is an absolute path with no atoms.
     *
     * @param Normalizer\PathNormalizerInterface|null $normalizer The normalizer
     *     to use when determining the result.
     *
     * @return boolean True if this path is the root path.
     */
    public function isRoot(
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        return !$this->normalize($normalizer)->hasAtoms();
    }

    /**
     * Determine if this path is the direct parent of the supplied path.
     *
     * @param AbsolutePathInterface                   $path       The child path.
     * @param Normalizer\PathNormalizerInterface|null $normalizer The normalizer
     *     to use when determining the result.
     *
     * @return boolean True if this path is the direct parent of the supplied
     *     path.
     */
    public function isParentOf(
        AbsolutePathInterface $path,
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null === $normalizer) {
            $normalizer = $this->createDefaultNormalizer();
        }

        return
            $path->hasAtoms() &&
            $this->normalize($normalizer)->atoms() ===
                $path->parent()->normalize($normalizer)->atoms();
    }

    /**
     * Determine if this path is an ancestor of the supplied path.
     *
     * @param AbsolutePathInterface                   $path       The child path.
     * @param Normalizer\PathNormalizerInterface|null $normalizer The normalizer
     *     to use when determining the result.
     *
     * @return boolean True if this path is an ancestor of the supplied path.
     */
    public function isAncestorOf(
        AbsolutePathInterface $path,
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null === $normalizer) {
            $normalizer = $this->createDefaultNormalizer();
        }

        $parentAtoms = $this->normalize($normalizer)->atoms();

        return $parentAtoms === array_slice(
            $path->normalize($normalizer)->atoms(),
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
     * @param AbsolutePathInterface $path The path that the generated path will
     *     be relative to.
     * @param Normalizer\PathNormalizerInterface|null $normalizer The normalizer
     *     to use when determining the result.
     *
     * @return RelativePathInterface A relative path from the supplied path to
     *     this path.
     */
    public function relativeTo(
        AbsolutePathInterface $path,
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null === $normalizer) {
            $normalizer = $this->createDefaultNormalizer();
        }

        $parentAtoms = $path->normalize($normalizer)->atoms();
        $childAtoms = $this->normalize($normalizer)->atoms();

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

        return $this->createPath($diffAtoms, false);
    }
}
