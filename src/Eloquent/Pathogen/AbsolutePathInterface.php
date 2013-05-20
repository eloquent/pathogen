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

interface AbsolutePathInterface extends PathInterface
{
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
    );

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
    );

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
    );

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
    );
}
