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

        if (1 === $numAtoms && static::SELF_ATOM === $atoms[0]) {
            return '';
        }

        return parent::name();
    }

    /**
     * Returns the parent of this path.
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

        $atoms = $this->atoms();
        $atoms[] = static::PARENT_ATOM;

        $path = $this->createPath($atoms, false);

        return $normalizer->normalize($path);
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

        return $this->createPath($this->atoms(), false, true);
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

    // Implementation details ==================================================

    /**
     * @param mixed<string> $atoms
     *
     * @return array<string>
     */
    protected function normalizeAtoms($atoms)
    {
        $atoms = parent::normalizeAtoms($atoms);
        if (count($atoms) < 1) {
            throw new Exception\EmptyPathException;
        }

        return $atoms;
    }
}
