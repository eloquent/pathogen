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
        $parentAtoms = $this->normalizer()->normalize($this)->atoms();
        $parentCount = count($parentAtoms);
        $childAtoms = $this->normalizer()->normalize($path)->atoms();

        if ($parentCount != count($childAtoms) - 1) {
            return false;
        }

        $loop = 0;
        while (
            array_key_exists($loop, $parentAtoms) &&
            $parentAtoms[$loop] === $childAtoms[$loop]
        ) {
            $loop++;
        }

        return $loop === $parentCount;
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
        $parentCount = count($parentAtoms);
        $childAtoms = $this->normalizer()->normalize($path)->atoms();

        if ($parentCount >= count($childAtoms)) {
            return false;
        }

        $loop = 0;
        while (
            array_key_exists($loop, $parentAtoms) &&
            $parentAtoms[$loop] === $childAtoms[$loop]
        ) {
            $loop++;
        }

        return $loop === $parentCount;
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
        $resultingAtoms = array();
        $parentAtoms = $this->normalizer()->normalize($this)->atoms();
        $parentCount = count($parentAtoms);
        $childAtoms = $path->normalizer()->normalize($path)->atoms();

        $commonAtoms = 0;
        while (
            array_key_exists($commonAtoms, $childAtoms) &&
            array_key_exists($commonAtoms, $parentAtoms) &&
            $parentAtoms[$commonAtoms] === $childAtoms[$commonAtoms]
        ) {
            $commonAtoms++;
        }

        for ($loop = $commonAtoms; $loop < $parentCount; $loop++) {
            $resultingAtoms[] = '..';
        }

        array_splice($childAtoms, 0, $commonAtoms);
        $resultingAtoms = array_merge($resultingAtoms, $childAtoms);

        return $this->factory()->createFromAtoms($resultingAtoms, false, false);
    }
}
