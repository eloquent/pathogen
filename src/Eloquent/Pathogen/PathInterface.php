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

interface PathInterface
{
    /**
     * Returns the atoms of this path as an array of strings.
     *
     * For example, the path '/foo/bar' has the atoms 'foo' and 'bar'.
     *
     * @return array<integer,string>
     */
    public function atoms();

    /**
     * Returns true if this path ends with a path separator.
     *
     * @return boolean
     */
    public function hasTrailingSeparator();

    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function string();

    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function __toString();

    /**
     * Returns the parent of this path.
     *
     * @return PathInterface
     * @throws Exception\RootParentExceptionInterface If this is method called on the root path.
     */
    public function parent();

    /**
     * Returns a new path with the supplied atom(s) suffixed to this path.
     *
     * @param string     $atom
     * @param string,... $additionalAtoms
     *
     * @return PathInterface
     */
    public function joinAtoms($atom);

    /**
     * Returns a new path with the supplied sequence of atoms suffixed to this
     * path.
     *
     * @param mixed<string> $atoms
     *
     * @return PathInterface
     */
    public function joinAtomSequence($atoms);

    /**
     * Returns a new path with the supplied path suffixed to this path.
     *
     * @param RelativePathInterface $path
     *
     * @return PathInterface
     */
    public function join(RelativePathInterface $path);

    const SEPARATOR = '/';
    const PARENT_ATOM = '..';
    const SELF_ATOM = '.';
}
