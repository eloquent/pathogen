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
 * Represents a relative path.
 */
class RelativePath extends AbstractPath implements RelativePathInterface
{
    // Implementation of RelativePathInterface =================================

    /**
     * Determine whether this path is the self path.
     *
     * The self path is a relative path with a single self atom (i.e. a dot
     * '.').
     *
     * @param Normalizer\PathNormalizerInterface|null $normalizer The normalizer to use when determining the result.
     *
     * @return boolean True if this path is the self path.
     */
    public function isSelf(
        Normalizer\PathNormalizerInterface $normalizer = null
    ) {
        if (null === $normalizer) {
            $normalizer = $this->createDefaultNormalizer();
        }

        $atoms = $this->normalize($normalizer)->atoms();

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
