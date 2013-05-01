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
        return $this->hasAtoms() ? parent::string() : '.';
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

        return 1 === $numAtoms && '.' === $atoms[0]
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
        $atoms[] = '..';

        $path = $this->factory()->createFromAtoms($atoms, false, false);

        return $this->normalizer()->normalize($path);
    }

    /**
     * Returns a new path instance with the supplied string suffixed to the last
     * path atom.
     *
     * @param string $suffix
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If the suffix causes the atom to be invalid.
     */
    public function suffixName($suffix)
    {
        // TODO: Throw exception
        $atoms = $this->atoms();
        if (1 === count($atoms) && '.' === $atoms[0]) {
            $atoms[0] = '.' . $suffix;

            return $this->factory()->createFromAtoms(
                $atoms,
                false,
                false
            );
        }

        return parent::suffixName($suffix);
    }

    /**
     * Returns a new path instance with the supplied string prefixed to the last
     * path atom.
     *
     * @param string $prefix
     *
     * @return PathInterface
     * @throws Exception\InvalidPathAtomExceptionInterface If the prefix causes the atom to be invalid.
     */
    public function prefixName($prefix)
    {
        // TODO: Throw exception
        $atoms = $this->atoms();
        if (1 === count($atoms) && '.' === $atoms[0]) {
            $atoms[0] = $prefix . '.';

            return $this->factory()->createFromAtoms(
                $atoms,
                false,
                false
            );
        }

        return parent::prefixName($prefix);
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
        return count($this->hasAtoms()) === 0;
    }

    /**
     * Returns true if this path is the self path.
     *
     * The self path is a relative path with a single self atom (i.e. a dot '.').
     *
     * @return boolean
     */
    public function isSelf()
    {
        $atoms = $this->atoms();

        return 1 === count($atoms) && '.' === $atoms[0];
    }
}
