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
    // implementation of PathInterface =========================================

    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function string()
    {
        return sprintf('/%s', parent::string());
    }

    /**
     * Returns the parent of this path.
     *
     * If this method is called on the root path, the root path will be
     * returned.
     *
     * @return PathInterface
     */
    public function parent()
    {
        $path = $this->normalizer()->normalize($this);
        if (!$path->hasAtoms()) {
            return $path;
        }

        $atoms = $path->atoms();
        array_pop($atoms);

        return $this->factory()->createFromAtoms($atoms, true, false);
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

        return $this->factory()->createFromAtoms(
            $this->atoms(),
            $this instanceof AbsolutePathInterface,
            true
        );
    }

    // implementation of AbsolutePathInterface =================================

    /**
     * Returns true if this path is the root path.
     *
     * The root path is an absolute path with no atoms.
     *
     * @return boolean
     */
    public function isRoot()
    {
        return !$this->hasAtoms();
    }

    /**
     * Returns true if this path is the direct parent of the supplied path.
     *
     * @param AbsolutePathInterface $path
     *
     * @return boolean
     */
    public function isParentOf(AbsolutePathInterface $path)
    {
        return
            $path->hasAtoms() &&
            $this->normalizer()->normalize($this)->atoms() === $path->parent()->atoms();
    }

    /**
     * Returns true if this path is an ancestor of the supplied path.
     *
     * @param AbsolutePathInterface $path
     *
     * @return boolean
     */
    public function isAncestorOf(AbsolutePathInterface $path)
    {
        $parentAtoms = $this->normalizer()->normalize($this)->atoms();

        return $parentAtoms === array_slice(
            $this->normalizer()->normalize($path)->atoms(),
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
     * @param AbsolutePathInterface $path
     *
     * @return RelativePathInterface
     */
    public function relativeTo(AbsolutePathInterface $path)
    {
        $parentAtoms = $this->normalizer()->normalize($path)->atoms();
        $childAtoms = $this->normalizer()->normalize($this)->atoms();
        $diff = array_diff_assoc($parentAtoms, $childAtoms);
        $fillCount = (count($childAtoms) - count($parentAtoms)) + count($diff);

        return $this->factory()->createFromAtoms(
            $fillCount > 0 ?
                array_merge(
                    array_fill(0, $fillCount, static::PARENT_ATOM),
                    $diff
                ) :
                $diff,
            false
        );
    }
}
