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
     * @param Normalizer\PathNormalizerInterface|null $normalizer
     *
     * @return boolean
     */
    public function isSelf(
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null === $normalizer) {
            $normalizer = new Normalizer\PathNormalizer;
        }

        $atoms = $normalizer->normalize($this)->atoms();

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
