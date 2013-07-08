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
 * The interface implemented by absolute paths.
 */
interface AbsolutePathInterface extends PathInterface
{
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
    );

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
    );

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
    );

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
    );
}
