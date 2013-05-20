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

class RelativePath extends AbstractPath implements RelativePathInterface
{
    /**
     * Returns a string representation of this path.
     *
     * @return string
     */
    public function string()
    {
        if ($this->hasAtoms()) {
            return parent::string();
        }

        if ($this->hasTrailingSeparator()) {
            return static::SELF_ATOM . static::ATOM_SEPARATOR;
        }

        return static::SELF_ATOM;
    }

    /**
     * Returns the last atom of this path.
     *
     * If this path has no atoms, or the only atom is a self atom, an empty
     * string is returned.
     *
     * @return string
     */
    public function name()
    {
        $atoms = $this->atoms();
        $numAtoms = count($atoms);

        return 1 === $numAtoms && static::SELF_ATOM === $atoms[0]
            ? ''
            : parent::name();
    }

    /**
     * Returns the parent of this path.
     *
     * @return PathInterface
     */
    public function parent()
    {
        $atoms = $this->atoms();
        $atoms[] = static::PARENT_ATOM;

        $path = $this->createPath($atoms, false);

        return $this->normalizer()->normalize($path);
    }

    /**
     * Returns a new path instance with a trailing slash suffixed to this path.
     *
     * @return PathInterface
     */
    public function joinTrailingSlash()
    {
        if ($this->hasTrailingSeparator()) {
            return $this;
        }

        if (!$this->hasAtoms()) {
            return $this->createPath(array(static::SELF_ATOM), false, true);
        }

        return $this->createPath($this->atoms(), false, true);
    }

    /**
     * Returns true if this path is the empty path.
     *
     * The empty path is a relative path with no atoms.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return !$this->hasAtoms();
    }

    /**
     * Returns true if this path is the self path.
     *
     * The self path is a relative path with a single self atom (i.e. a dot
     * '.').
     *
     * @return boolean
     */
    public function isSelf()
    {
        $atoms = $this->atoms();

        return 1 === count($atoms) && static::SELF_ATOM === $atoms[0];
    }
}
