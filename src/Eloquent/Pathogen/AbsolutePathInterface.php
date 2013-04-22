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
     * @return boolean
     */
    public function isRoot();

    /**
     * Returns true if this path is the direct parent of the supplied path.
     *
     * @param AbsolutePathInterface $path
     *
     * @return boolean
     */
    public function isParentOf(AbsolutePathInterface $path);

    /**
     * Returns true if this path is an ancestor of the supplied path.
     *
     * @param AbsolutePathInterface $path
     *
     * @return boolean
     */
    public function isAncestorOf(AbsolutePathInterface $path);

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
    public function relativeTo(AbsolutePathInterface $path);
}
