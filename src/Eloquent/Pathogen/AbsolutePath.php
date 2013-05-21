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

class AbsolutePath extends AbstractPath implements AbsolutePathInterface
{
    // Implementation of PathInterface =========================================

    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function string()
    {
        return sprintf('%s%s', static::ATOM_SEPARATOR, parent::string());
    }

    /**
     * Returns the parent of this path.
     *
     * If this method is called on the root path, the root path will be
     * returned.
     *
     * @param Normalizer\PathNormalizerInterface|null $normalizer
     *
     * @return PathInterface
     */
    public function parent(
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null == $normalizer) {
            $normalizer = new Normalizer\PathNormalizer;
        }

        $path = $normalizer->normalize($this);
        if (!$path->hasAtoms()) {
            return $path;
        }

        $atoms = $path->atoms();
        array_pop($atoms);

        return $this->createPath($atoms, true);
    }

    /**
     * Returns a new path instance with a trailing slash suffixed to this path.
     *
     * @return PathInterface
     */
    public function joinTrailingSlash()
    {
        if ($this->hasTrailingSeparator() || !$this->hasAtoms()) {
            return $this;
        }

        return $this->createPath($this->atoms(), true, true);
    }

    // Implementation of AbsolutePathInterface =================================

    /**
     * Returns true if this path is the root path.
     *
     * The root path is an absolute path with no atoms.
     *
     * @param Normalizer\PathNormalizerInterface|null $normalizer
     *
     * @return boolean
     */
    public function isRoot(
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null == $normalizer) {
            $normalizer = new Normalizer\PathNormalizer;
        }

        return !$normalizer->normalize($this)->hasAtoms();
    }

    /**
     * Returns true if this path is the direct parent of the supplied path.
     *
     * @param AbsolutePathInterface                   $path
     * @param Normalizer\PathNormalizerInterface|null $normalizer
     *
     * @return boolean
     */
    public function isParentOf(
        AbsolutePathInterface $path,
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null == $normalizer) {
            $normalizer = new Normalizer\PathNormalizer;
        }

        return
            $path->hasAtoms() &&
            $normalizer->normalize($this)->atoms() === $path->parent()->atoms();
    }

    /**
     * Returns true if this path is an ancestor of the supplied path.
     *
     * @param AbsolutePathInterface                   $path
     * @param Normalizer\PathNormalizerInterface|null $normalizer
     *
     * @return boolean
     */
    public function isAncestorOf(
        AbsolutePathInterface $path,
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null == $normalizer) {
            $normalizer = new Normalizer\PathNormalizer;
        }

        $parentAtoms = $normalizer->normalize($this)->atoms();

        return $parentAtoms === array_slice(
            $normalizer->normalize($path)->atoms(),
            0,
            count($parentAtoms)
        );
    }

    /**
     * Returns a relative path from the supplied path to this path.
     *
     * For example, given path A equal to '/foo/bar', and path B equal to
     * '/foo/baz', A relative to B would be '../bar'.
     *
     * @param AbsolutePathInterface                   $path
     * @param Normalizer\PathNormalizerInterface|null $normalizer
     *
     * @return RelativePathInterface
     */
    public function relativeTo(
        AbsolutePathInterface $path,
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null == $normalizer) {
            $normalizer = new Normalizer\PathNormalizer;
        }

        $parentAtoms = $normalizer->normalize($path)->atoms();
        $childAtoms = $normalizer->normalize($this)->atoms();

        if ($childAtoms === $parentAtoms) {
            $diffAtoms = array(static::SELF_ATOM);
        } else {
            $diffAtoms = array_diff_assoc($parentAtoms, $childAtoms);
            $fillCount =
                (count($childAtoms) - count($parentAtoms)) +
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
